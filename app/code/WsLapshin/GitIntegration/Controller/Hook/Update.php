<?php
namespace WsLapshin\GitIntegration\Controller\Hook;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Action;
use Magento\TestFramework\Event\Magento;
use Magento\Framework\App\Action\Context;
use WsLapshin\GitIntegration\Helper\Text;
use Magento\Catalog\Model\ProductRepository;
use WsLapshin\GitIntegration\Helper\LogHandler;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Update extends Action implements
                            CsrfAwareActionInterface,
                            HttpGetActionInterface,
                            HttpPostActionInterface
{
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

    private $_directorylist;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        LogHandler $loggerHandler,
        ScopeConfigInterface $scopeConfig,
	ProductRepository $productRepo,
	\Magento\Framework\App\Filesystem\DirectoryList $_directorylist
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
	$this->_directorylist = $_directorylist;
        
        $logger->pushHandler($loggerHandler);
        $this->logger = $logger;
        $this->productRepo = $productRepo;

        $this->requestBody = $this->_request->getContent();
        $this->requestJson = json_decode($this->requestBody, true);
        $this->responseResult = $this->resultFactory->create('json');
    }

    public function execute()
    {
        if( $requestHasErrors = $this->requestHasErrors() ){
                $this->logger->error($requestHasErrors['message']);
                $this->response(['error'=>$requestHasErrors['message']], $requestHasErrors['code']);
        }

        $wikiUrl = $this->requestJson['pages'][0]['html_url'];
	    $wikiTitle = $this->requestJson['pages'][0]['title'];
        
	    sleep(2);
        if(!$wikiDocument = Text::getDocumentByUrl($wikiUrl)){
            $error = 'Cant find repo url, bad hook request';
            $this->logger->error($error);
            $this->response(['error'=> $error ], 400);
        } 
        $this->logger->debug($wikiDocument->getText());
        $skuGroups = $this->getTagData($wikiDocument); 

        //try to find saved document
        $cacheFilename = Text::getCacheFilename($wikiUrl, $this->scopeConfig, $this->_directorylist);
        $cachedDocument = Text::getDocumentByUrl($cacheFilename);
        
        $cachedSkuGroups = [];
        if($cachedDocument) {
            $cachedSkuGroups = $this->getTagData($cachedDocument);
        }

        $this->deleteSku($skuGroups, $cachedSkuGroups, $wikiUrl);
        $updateResult = $this->updateSku($skuGroups,$wikiUrl, $wikiTitle);


        if (true !== $updateResult) {
            $error = 'Not updated. Server errors. See logs';
            $this->logger->error($error);
            $this->response(['error'=> $error], 500);
        }

        //save document to cache
        $wikiDocument->persist($cacheFilename);

        $this->response(['success'=>'ok'], 201);
    }

    /**
     * Отдает сгруппированные по DocType артикулы, в которые необходимо
     * вставить ссылку на wiki-страницу
     */
    private function getTagData(Text $document)
    {
        
        $skuKey = $this->scopeConfig->getValue('gitintegration/parser_filter/allowed_atrs/sku');
        $doctypeKey = $this->scopeConfig->getValue('gitintegration/parser_filter/allowed_atrs/doctype');
        $allowedAtributes = explode(',', $this->scopeConfig->getValue('gitintegration/parser_filter/allowed_atrs/other'));
        $allowedAtributes = array_merge($allowedAtributes, [$skuKey, $doctypeKey]);
        //Exception bad config @todo
        $requiredAtributes = explode(',', $this->scopeConfig->getValue('gitintegration/parser_filter/required_atrs'));
        //Exception bad config @todo
        $allowedDocTypes = explode(',', $this->scopeConfig->getValue('gitintegration/parser_filter/allowed_types'));
        //Exception bad config @todo

        $data = [];
        foreach($allowedDocTypes as $dc) {
            $data[$dc] = [];
        }
        $parsed = $document->parseCommentTags();

        foreach($parsed as $tagData) {
            $rawTag = $tagData[Text::PARSER_RAW_TAG_RESULT_KEY];
            unset($tagData[Text::PARSER_RAW_TAG_RESULT_KEY]);

            /** Не распознанные символы */
            if(!empty($tagData[Text::PARSER_UNRECOGNIZED_RESULT_KEY])) {
                $this->logger->debug('parsed tag ' . $rawTag . ' has unrecognized symbols ' . 
                    print_r($tagData[Text::PARSER_UNRECOGNIZED_RESULT_KEY], true) );
            }
            
            unset($tagData[Text::PARSER_UNRECOGNIZED_RESULT_KEY]);
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
                $this->logger->debug('parsed tag ' . $rawTag . ' has not-allowed atributes ');
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
            $doctype = $tagData[$doctypeKey];
           

            foreach( $skus as $s) {
                if( !empty($s) ) {
                    if(!in_array($s, $data[$doctype])) {
                        $data[$doctype][] = $s;
                    }
                } else {
                    $this->logger->debug('Empty sku was given in sku atribute, of ' .$rawTag. 'skip this data');
                }
            }

            $this->logger->info('Processed tag' . $rawTag );
            //@todo log in csv on info level
        }
        return $data;
    }

    private function deleteSku($skuGroups, $cachedSkuGroups, $wikiUrl)
    {
        if( empty($cachedSkuGroups) ) {
            return true;
        }

        $clearedLog = [];

        // here goes logic of diff $skuGroups and $cachedSkuGroups
        foreach($cachedSkuGroups as $doctype=>$cachedSkus) {
            $newSkus = $skuGroups[$doctype];
            $skusToClear = array_diff($cachedSkus, $newSkus);
            foreach($skusToClear as $clearThisSku) {
                /** @var Product */
                try{
                    $product = $this->productRepo->get($clearThisSku, true);
                } catch( Exception $e) {
                    continue;
                }

                $description = new Text($product->getData('description'));
                if( null == $description->getBlock($doctype) ) {
                    continue;
                }
                $oldBlockContent = $description->getBlockContent($doctype);
                $escapedUrl = preg_quote($wikiUrl); 

                $newBlockContent = preg_replace('~<a\s+href="'.$escapedUrl.'".*>.*<\/a>(<br\/>|)~U', "", $oldBlockContent);
                $description->setBlockContent($doctype, $newBlockContent);
                $product->setData('description', $description->getText());
                $this->productRepo->save($product);
                $clearedLog[] = $clearThisSku;
            }
        }

        if( !empty($clearedLog)) {
            $this->writeCSVLog('delete', $wikiUrl, $clearedLog);
        }

        return true;

    }

    /**
     * Вставлят в описание каждого sku в разделы skuGroups ссылку на documentUrl
     */
    private function updateSku($skuGroups, $wikiUrl, $wikiTitle)
    {
        $addedLog = [];
        foreach($skuGroups as $docType=>$skus) {
            foreach($skus as $s) {
                try {
                    /** @var Product */
                    $product = $this->productRepo->get($s, true);
                } catch (\Exception $e) {
                    $this->logger->warning('Sku ' . $s . ' not found while updating. Skip');
                    continue;
                }
                $description = new Text($product->getData('description'));

                
                //modify text block responsible for doctype
                if( null == $description->getBlock($docType)) {
                    $description->addBlock($docType, $description->getLength());
                    $oldBlockContent = "<h4 id='".$docType."'>".ucfirst($docType)."s</h4>";
                } else {
                    $oldBlockContent = $description->getBlockContent($docType);

                    //check that link is alerady in block;
                    if( false !== strpos($oldBlockContent, $wikiUrl) ) {
                        continue;
                    }
                }
                $linkContent = '<a href="' . $wikiUrl . '" target="_blank">'. $wikiTitle . '</a><br/>';
                $newContent = $oldBlockContent . $linkContent;
                $description->setBlockContent($docType, $newContent);
               
                //create ancors if neccessary
                if( null == $description->getBlock('ancors')) {
                    $description->addBlock('ancors', 0);
                    $description->setBlockContent('ancors', '<a href="#tutorial">Tutorials</a><a href="#project" style="margin-left:25px">Projects</a>');
                }

                $descriptionResult = $description->getText();
                $product->setData('description', $descriptionResult);
                $this->productRepo->save($product);
                $addedLog[] = $s;
            }
        }
        if( !empty($addedLog)){
            $this->writeCSVLog('add', $wikiUrl, $addedLog);
        }
        return true;
    }

    private function writeCSVLog($operation, $url, array $skus)
    {
        //@todo functionality to disable csv logging in config

        $path = $this->_directorylist->getPath('var') . "/" .  $this->scopeConfig->getValue('gitintegration/logging/csv_file');
        $f = fopen($path, 'a+');
        if( !$f) {
            return false;
        }
        fputcsv($f, [$operation, $url, implode(",", $skus)], ";");
        fclose($f);
    }

    /**  */
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

        //@debug
        /** */
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
