<?php
namespace WsLapshin\GitIntegration\Helper;

/**
 * Helper methods for manipualating text strings
 */
class Text  
{
    const PARSER_UNRECOGNIZED_RESULT_KEY = '__unrecoginzed';

    const PARSER_RAW_TAG_RESULT_KEY = '__tag';

    /** @var $text string - some text */
    private $text;

    private $blocks;

    private $length;

    private static $BLOCK_ATTR_PREFIX = "data-block-";

    private static $BLOCK_TEMPLATE = "<div %attr% >%content%</div>";

    public function __construct($text)
    {
        $this->text = $text; 
        $this->blocks = [];
        $this->length = strlen($this->text);
    }

    public function getText()
    {
        return $this->text;
    }

    public function getLength()
    {
        return $this->length;
    }

    /** Методы работы с логическими блоками текста */

    /**
     * Get blocks' info:
     *       'start_block'=> start pos of a block
     *       'finish_block'=> finish pos of a block
     *       'start_content'=> start pos of content of a block
     *       'finish_content'=> finish pos of content of a block
     *       'content'=> content
     * @return array
     */
    public function getBlock($blockName)
    {
        if( isset($this->blocks[$blockName])) {
            return $this->blocks[$blockName];
        }
        if( $this->parseBlock($blockName) ) {
            return $this->blocks[$blockName];
        }
        return null;
    }

    public function getBlockContent($blockName)
    {
        $block = $this->getBlock($blockName);
        if ($block) {
            return $block['content'];
        }
        return null;
    }

    public function getBlockPosition($blockName)
    {
        $block = $this->getBlock($blockName);
        if ($block) {
            return $block['start_block'];
        }
        return null;
    }

    public function getBlockContentPosition($blockName)
    {
        $block = $this->getBlock($blockName);
        if ($block) {
            return $block['start_content'];
        }
        return null;
    }

    public function setBlockContent($blockName, $content)
    {

        $block = $this->getBlock($blockName);
        if(null === $block) {
            return false;
        }

        $start = $block['start_content'];
        if( null== $block['finish_content'] ) {
            $length = 0;
        } else {
            $length = $block['finish_content'] - 
                    $block['start_content'] + 1;
        }
        $this->text = substr_replace($this->text, $content, $start, $length);
        $this->length = strlen($this->text);

        $this->clearCache();
        return true;
    }

    /**
     * Если блок уже существует, действие игнорируется
     */
    public function addBlock($blockName, $position)
    {
        if($this->getBlock($blockName)) {
            return false;
        }

        $attr=$this->getBlockAttr($blockName);
        $blockWrap = str_replace(["%attr%", "%content%"],[$attr,""],self::$BLOCK_TEMPLATE);
        $this->text = substr_replace($this->text, $blockWrap, $position, 0);
        $this->length = strlen($this->text);
        $this->clearCache();
        return true; 
    }

    private function clearCache()
    {
        $this->blocks = [];
    }

    private function parseBlock($blockName)
    {
        $regExp = $this->getBlockRegex($blockName);
        if(!preg_match_all($regExp, $this->text, $matches, PREG_OFFSET_CAPTURE)){
            unset($this->blocks[$blockName]);//if existst somehow
            return false;
        }

        $matchSeq = 0; // @todo продумать работу для неск. блоков
        
        $startBlock = $matches[0][$matchSeq][1];
        $lengthBlock = strlen($matches[0][$matchSeq][0]);
        $finishBlock = $startBlock + $lengthBlock - 1;

        $content = $matches[1][$matchSeq][0];
        $lengthContent = strlen($matches[1][$matchSeq][0]);
        $startContent = $matches[1][$matchSeq][1];
        
        if(0 != $lengthContent){
            $finishContent = $startContent + $lengthContent - 1;
        } else {
            $finishContent = null; 
            // $startContent++;
        }

        $block = [
            'start_block'=>$startBlock,
            'finish_block'=>$finishBlock,
            'start_content'=>$startContent,
            'finish_content'=>$finishContent,
            'content'=>$content
        ];
        $this->blocks[$blockName] = $block;

        return true;
    }

    private function getBlockRegex($blockName)
    {
        $attr=$this->getBlockAttr($blockName);
        return "/" . str_replace(
                        ["%attr%", "%content%","/"],[$attr,"(.*?)","\/"],
                        self::$BLOCK_TEMPLATE) . "/s";
    }

    private function getBlockAttr($blockName)
    {
        return  self::$BLOCK_ATTR_PREFIX . $blockName;
    }

    /** Метод для парсинга тегов комментариев в тексте */
    public function parseCommentTags()
    {
        $document = $this->text;
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

    /**
     * @return Text
     */
    public static function getDocumentByUrl($documentUrl)
    {
        //@todo use curl and process http code exceptions (for bad urls given from git maybe)
        $doc = file_get_contents($documentUrl);
        if (false === $doc) {
            return false;
        }
        return new static($doc);
    }
} 