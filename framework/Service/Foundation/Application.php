<?php

namespace Framework\Service\Foundation;

use Framework\Service\Http\HttpRequest;
use Framework\Service\Http\ConsoleRequest;
use Framework\Provider\Log\LogServiceProvider;
use Framework\Contract\Http\Request as RequestContract;
use Framework\Provider\Exception\ExceptionServiceProvider;

/**
 * 应用
 */
class Application extends Container {

    /**
     * 已加载过的服务提供者
     * @var array
     */
    protected $arrLoadedProviders = [];

    /**
     * 延迟服务提供者
     */
    protected $arrDeferredServices = [];

    /**
     * 创建应用实例
     */
    public function __construct($strBasePath) {
        $this->setBasePath($strBasePath);

        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
    }

    /**
     * 设置应用路径
     * @param  string  $basePath
     */
    public function setBasePath($strBasePath) {
        $strBasePath = rtrim($strBasePath, '\/');

        $this->instance('path.base', $strBasePath);
        $this->instance('path.app', $strBasePath . DIRECTORY_SEPARATOR . 'app');
        $this->instance('path.config', $strBasePath . DIRECTORY_SEPARATOR . 'config');
        $this->instance('path.framework', $strBasePath . DIRECTORY_SEPARATOR . 'framework');
        $this->instance('path.resource', $strBasePath . DIRECTORY_SEPARATOR . 'resource');
        $this->instance('path.storage', $strBasePath . DIRECTORY_SEPARATOR . 'storage');
        $this->instance('path.web', $strBasePath . DIRECTORY_SEPARATOR . 'web');
    }

    /**
     * 注册基本绑定
     */
    private function registerBaseBindings() {
        static::setInstance($this);

        $this->instance(Application::class, $this);

        if ($this->runningInConsole()) {
            $this->singleton(RequestContract::class, ConsoleRequest::class);
        } else {
            $this->singleton(RequestContract::class, HttpRequest::class);
        }
    }

    /**
     * 注册基本的服务提供者
     */
    protected function registerBaseServiceProviders() {
        $this->register(new LogServiceProvider($this));
        $this->register(new ExceptionServiceProvider($this));
    }

    /**
     * 从容器中解析实例
     * @param string $strAbstract 类别名，实际类名，接口类名
     * @param array $arrParameters 解析实例需要的参数，如构造函数里的参数
     */
    public function make($strAbstract, $arrParameters = []) {
        //加载延迟服务
        if (isset($this->arrDeferredServices[$strAbstract]) && !isset($this->arrInstances[$strAbstract])) {
            $this->loadDeferredProvider($strAbstract);
        }

        //调用父类make
        return parent::make($strAbstract, $arrParameters);
    }

    /**
     * 加载延迟服务提供者
     * @param  string  $strAbstract
     */
    protected function loadDeferredProvider($strAbstract) {
        //服务是否为延迟服务
        if (!isset($this->arrDeferredServices[$strAbstract])) {
            return;
        }

        //获取服务提供者
        $strProvider = $this->arrDeferredServices[$strAbstract];

        //如果没加载过，则加载
        if (!isset($this->arrLoadedProviders[$strProvider])) {
            unset($this->arrDeferredServices[$strAbstract]);

            //使用服务提供者实例进行注册
            $this->register($instance = new $strProvider($this));
        }
    }

    /**
     * 注册服务提供者
     * @param mixed $mixProvider
     */
    public function register($mixProvider) {
        $strProvider = is_string($mixProvider) ? $mixProvider : get_class($mixProvider);

        //是否已注册过
        if (array_key_exists($strProvider, $this->arrLoadedProviders)) {
            return;
        }

        //获取服务提供者实例
        if (is_string($mixProvider)) {
            $mixProvider = new $mixProvider($this);
        }
        //如果服务提供者有register方法则执行
        if (method_exists($mixProvider, 'register')) {
            //此方法将具体服务的绑定到服务容器
            $mixProvider->register();
        }

        //如果服务提供者有bindings或singletons属性，则将服务绑定到服务容器 
        //普通绑定
        if (property_exists($mixProvider, 'bindings')) {
            foreach ($mixProvider->bindings as $key => $value) {
                $this->bind($key, $value);
            }
        }
        //单例绑定
        if (property_exists($mixProvider, 'singletons')) {
            foreach ($mixProvider->singletons as $key => $value) {
                $this->singleton($key, $value);
            }
        }

        //标记服务提供者为已注册
        $this->arrLoadedProviders[$strProvider] = true;
    }

    /**
     * 运行需要启动的类
     * @param array $arrBootstrappers 启动的类
     */
    public function bootstrapWith($arrBootstrappers) {
        foreach ($arrBootstrappers as $strBootstrapper) {
            $this->make($strBootstrapper)->bootstrap($this);
        }
    }

    /**
     * 注册服务提供者
     */
    public function registerConfiguredProviders() {
        $arrProvider = $this->make('config')->get('app.provider');

        foreach ($arrProvider as $strProvider) {
            $objProvider = new $strProvider($this);
            if ($objProvider->isDeferred()) {
                //延迟服务，记录
                foreach ($objProvider->provides() as $strService) {
                    $this->arrDeferredServices[$strService] = $strProvider;
                }
            } else {
                //即使服务，直接注册
                $this->register($objProvider);
            }
        }
    }

    /**
     * 应用是否在控制台运行
     */
    public function runningInConsole() {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }

}
