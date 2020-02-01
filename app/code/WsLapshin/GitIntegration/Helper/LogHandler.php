<?php
namespace WsLapshin\GitIntegration\Helper;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class LogHandler extends Base
{
    protected $fileName;

    protected $loggerType;

    /**
     * __construct
     *
     * @param  mixed $filesystem
     * @param  mixed $scopeConfig
     *
     * @return void
     */
    public function __construct(DriverInterface $filesystem, ScopeConfigInterface $scopeConfig)
    {
        $logFile = $scopeConfig->getValue('gitintegration/logging/log_file');
        $this->fileName = $logFile;

        $oClass = new \ReflectionClass('\Monolog\Logger');
        $constants = $oClass->getConstants();
        $loglevelTxt = $scopeConfig->getValue('gitintegration/logging/log_level');
        $this->loggerType = $constants[$loglevelTxt]; 
        parent::__construct($filesystem,null,null);

        $this->bubble = false;
    }
}