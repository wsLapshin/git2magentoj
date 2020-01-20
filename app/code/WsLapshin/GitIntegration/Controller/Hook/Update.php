<?php
namespace WsLapshin\GitIntegration\Controller\Hook;

use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Action;
use WsLapshin\GitIntegration\LogHandler;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Update extends Action implements
                            CsrfAwareActionInterface,
                            HttpGetActionInterface,
                            HttpPostActionInterface
{

    const PARSER_UNRECOGNIZED_RESULT_KEY = '__unrecoginzed';

    const PARSER_RAW_TAG_RESULT_KEY = '__tag';

    /** @var \Magento\Framework\App\Request */
    protected $_request;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    private $scopeConfig;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    private $requestJson;

    private $requestBody;

    private $responseResult;

    private $productRepo;


    public function __construct(
        Context $context,
        LoggerInterface $logger,
        LogHandler $loggerHandler,
        ScopeConfigInterface $scopeConfig,
        ProductRepository $productRepo 
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        
        $logger->pushHandler($loggerHandler);
        $this->logger = $logger;
        $this->productRepo = $productRepo;
    }

    public function execute()
    {
        $this->requestBody = $this->_request->getContent();
        $this->requestJson = json_decode($this->requestBody, true);
        $this->responseResult = $this->resultFactory->create('json');

        $requestHasErrors = $this->requestHasErrors();
        if ($requestHasErrors) {
            $this->logger->error($requestHasErrors['message']);
            $this->response(['error'=>$requestHasErrors['message']], $requestHasErrors['code']);
        }

        $documentUrl = $this->requestJson['pages'][0]['html_url'] ;
        $document = $this->getDocument($documentUrl . ".md"); //
        if (false === $document) {
            $error = 'Cant find repo url, bad hook request';
            $this->logger->error($error);
            $this->response(['error'=> $error ], 400);
        }

        $this->logger->debug($document);

        $parsed = $this->parseDocument($document);

        //for @debug purpose only with false flag
        //$data = $this->getTagData($parsed, false);
        $tags = $this->getTagData($parsed); //
        if (empty($tags[0])) {
            $message = 'No sku tags were found';
            $this->logger->info($message);
            $this->response(['success'=>$message], 200);
        }


        $updateResult = $this->updateSku($tags[0], $documentUrl);
        if (true !== $updateResult) {
            $error = 'Not updated. Server errors. See logs';
            $this->logger->error($error);
            $this->response(['error'=> $error], 500);
        }

        $this->response(['success'=>'ok'], 201);
    }

    private function requestHasErrors()
    {
        if (null === $this->requestJson) {
            return ['message' => "Bad request. Use valid application/json", 'code'=>400];
        }

        $requestMethod = $this->_request->getMethod();
        $allowedMethod = $this->scopeConfig->getValue('gitintegration/request_filter/type');
        if ($requestMethod !== $allowedMethod) {
            return ['message'=> "Method not allowed", 'code'=>405];
        }

        $requestSignature = $this->_request->getHeader('X-Hub-Signature');
        $secret = $this->scopeConfig->getValue('gitintegration/repo_filter/secret');
        $hash = 'sha1=' . hash_hmac("sha1", $this->requestBody, $secret);
        //$hash = 'sha1=5e07c1ed4aacce04ee2bce0b718e4af6de7bb485'; //@debug proposes
        if ($requestSignature !==  $hash) {
            return ['message' => "Permission denied. Bad auth token", 'code'=>403];
        }

        if (empty($this->requestJson['pages'][0]['html_url'])) {
            return ['message' => "Bad request. body.pages.0.html_url field is empty in request", 'code'=>400];
        }
        if (empty($this->requestJson['repository']['full_name'])) {
            return ['message' => "Bad request. body.repository.full_name field is empty in request", 'code'=>400];
        }

        $requestRepo = $this->requestJson['repository']['full_name'];
        $allowedRepo = $this->scopeConfig->getValue('gitintegration/repo_filter/repo');
        if ($requestRepo !== $allowedRepo) {
            return ['message' => "Permission denied. Foreign repository", 'code'=>403];
        }

        $requestEvent = $this->_request->getHeader('X-Github-Event');
        $allowedEvent = $this->scopeConfig->getValue('gitintegration/request_filter/event');
        if ($requestEvent !== $allowedEvent) {
            return ['message' => "Skip event, not allowed", 'code'=>405];
        }

        return false;
    }

    private function getDocument($documentUrl)
    {
        //test only
        // $documentUrl = __DIR__ . '/../../Test/document.md';
        // $documentUrl = __DIR__ . '/../../Test/empty.md';

        //@todo use curl and process http code exceptions (for bad urls given from git maybe)
        sleep(2);
        return file_get_contents($documentUrl);
    }

    private function parseDocument($document)
    {
        /** @see https://superuser.com/questions/1153239/regex-to-match-xml-comments */
        $regExp = "/<!--[\s\S\n]*?-->/";
        $matches = [];
        preg_match_all($regExp, $document, $matches);
        $documentComments = $matches[0];

        $result = [];
        foreach ($documentComments as $tag) {
            $tmp = strtolower($tag);
            $tmp = preg_replace('/\s/', '~', $tmp); //normalize all no-print chars to whitespaces (сейчас ~ вместо whitespaces)
            $tmp = preg_replace('/~{1,}/', '~', $tmp); // two or more whitespaces to one
            $tmp = preg_replace(['/<!--~*/', '/~*-->/'], ['',''], $tmp); //strip tags
            $words = explode('~', $tmp);

            // prepare array for storing info
            $unrecognizedKey =  self::PARSER_UNRECOGNIZED_RESULT_KEY; 
            $tagKey = self::PARSER_RAW_TAG_RESULT_KEY; 
            $parsedTag = [];
            $parsedTag[$unrecognizedKey] = null;
            $parsedTag[$tagKey] = $tag;

            foreach ($words as $w) {
                if (preg_match('/^\w+:\S+$/', $w)) {
                    $kv = explode(':', $w);
                    $k = $kv[0];
                    $v = $kv[1];
                    $parsedTag[$k] = $v;
                } else {
                    $parsedTag[$unrecognizedKey][] = $w;
                }
            }

            $result[] = $parsedTag;
        }

        return $result;
    }

    private function getTagData($parsed, $processOnlyFirstValidTag = true)
    {
        $data = [];
        $skuKey = $this->scopeConfig->getValue('gitintegration/parser_filter/allowed_atrs/sku');
        $doctypeKey = $this->scopeConfig->getValue('gitintegration/parser_filter/allowed_atrs/doctype');
        $allowedAtributes = explode(',', $this->scopeConfig->getValue('gitintegration/parser_filter/allowed_atrs/other'));

       
        $allowedAtributes = array_merge($allowedAtributes, [$skuKey, $doctypeKey]);
        //Exception bad config @todo
        $requiredAtributes = explode(',', $this->scopeConfig->getValue('gitintegration/parser_filter/required_atrs'));
        //Exception bad config @todo
        $allowedDocTypes = explode(',', $this->scopeConfig->getValue('gitintegration/parser_filter/allowed_types'));
        //Exception bad config @todo


        foreach($parsed as $tagData) {
            $rawTag = $tagData[self::PARSER_RAW_TAG_RESULT_KEY];
            unset($tagData[self::PARSER_RAW_TAG_RESULT_KEY]);

            /** Не распознанные символы */
            if(!empty($tagData[self::PARSER_UNRECOGNIZED_RESULT_KEY])) {
                $this->logger->debug('parsed tag ' . $rawTag . ' has unrecognized symbols ' . 
                    print_r($tagData[self::PARSER_UNRECOGNIZED_RESULT_KEY], true) );
            }
            
            unset($tagData[self::PARSER_UNRECOGNIZED_RESULT_KEY]);
            $tagDataKeys = array_keys($tagData);

            /** Если передан тег, в котором нет хотя бы одного интересующего нас атрибута, значит этот тег нас вообще не касается */
            if( empty($tagDataKeys) ) {
                $this->logger->debug('parsed tag ' . $rawTag . ' has not atributes, skip... ');
                continue;
            }
            $possibleTags = array_merge($allowedAtributes, $requiredAtributes); // @todo защита от неверного конфига
            $needToProcess = false;
            foreach($possibleTags as $pt) {
                if(in_array($pt, $tagDataKeys)) {
                    $needToProcess = true;
                    break;
                }
            }
            if(!$needToProcess) {
                $this->logger->debug('parsed tag ' . $rawTag . ' has not needed atributes, skip... ');
                continue;
            }

            /** Не поддерживаемые аттрибуты */
            if( !empty( array_diff($tagDataKeys, $allowedAtributes) ) ) {
                $this->logger->debug('parsed tag ' . $rawTag . ' has not allowed atributes ');
            }

            /** Отсутствуют обязательные аттрибуты */
            $requiredDiff = array_diff($requiredAtributes, $tagDataKeys );
            if( !empty($requiredDiff) ) {
                $this->logger->warning('parsed tag ' . $rawTag . ' has not required atributes. Continue... ' . 
                  print_r($requiredDiff, true) );
                continue;
            }

            /** Неподдерживаемый doctype */
            if( !isset($tagData[$doctypeKey]) ) {
                $this->logger->error('doctype atribute is always needed for this app. Check etc/config and add declaration to required_atrs');
            }
            if( !in_array($tagData['doctype'], $allowedDocTypes) ) {
                $this->logger->warning('parsed tag ' . $rawTag . ' has not supported doctype. Skip... ');
                continue;
            }

            /** Невалидный sku */
            if( !isset($tagData[$skuKey]) ) {
                $this->logger->error('sku atribute is always needed for this app. Check etc/config and add declaration to required_atrs');
            }
            //@todo проверить sku на соответствие системе номенклатуры магазина
           
            //попытка explode sku
            $skus = explode(',', $tagData[$skuKey]);
            $tagData['sku'] = [];
            foreach( $skus as $s) {
                if( !empty($s) ) {
                    $tagData['sku'][] = $s;
                } else {
                    $this->logger->debug('Empty sku was given in sku atribute, of ' .$rawTag. 'skip this data');
                }
            }

            $data[] = $tagData;
            $this->logger->info('Processed tag' . $rawTag );
            //@todo log in csv on info level
            if($processOnlyFirstValidTag) {
                break;
            } 
        }
        return $data;
    }

    private function updateSku($data, $documentUrl)
    {
        /** @var Product */
        foreach($data['sku'] as $s) {
            try {
                $product = $this->productRepo->get($s, true);
            } catch (\Exception $e) {
                $this->logger->warning('Sku ' . $s . ' not found while updating. Skip');
                continue;
            }
            $description = $product->getData('description');
            $description .= '<br/><a href="' . $documentUrl . '" target="_blank">'. $documentUrl .'</a>';
            $product->setData('description', $description);
            $this->productRepo->save($product);
        }
       

        //fetch products and their descriptions

        //for Tutorials:
        //checkTutorialsAncor - create if necessary
        //checkTutorialsSection - create if necessary
        //add link -> (flush)
        //log

        //for Projects:
        //checkProjectAncor - create if necessary
        //checkProjectSection - create if necessary
        //add link -> (flush)
        //log

        return true;
    }

    private function response($data, $code)
    {
        $this->responseResult->setData($data);
        $this->responseResult->setHttpResponseCode($code);
        $this->responseResult->renderResult($this->_response);
        $this->_response->sendResponse();
        exit;
    }

    public function validateForCsrf(\Magento\Framework\App\RequestInterface $request): ?bool
    {
        return true;
    }

    public function createCsrfValidationException(\Magento\Framework\App\RequestInterface $request): ?\Magento\Framework\App\Request\InvalidRequestException
    {
        return null;
    }
}
