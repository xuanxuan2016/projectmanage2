<?php

namespace Framework\Service\Foundation;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionParameter;
use Framework\Facade\Log;
use Framework\Service\Exception\ApplicationException;

/**
 * 容器
 */
class Container {

    /**
     * 容器实例
     */
    protected static $objInstance;

    /**
     * 共享实例
     * @var array
     */
    protected $arrInstances = [];

    /**
     * 绑定关系
     * @var array
     */
    protected $arrBindings = [];

    /**
     * 构建服务参数的堆栈
     * @var array
     */
    protected $arrWith = [];

    /**
     * 构建服务的堆栈
     * @var array
     */
    protected $arrBuildStack = [];

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    private function __wakeup() {
        
    }

    /**
     * 获取容器实例
     */
    public static function getInstance() {
        if (is_null(static::$objInstance)) {
            static::$objInstance = new static;
        }
        return static::$objInstance;
    }

    /**
     * 设置容器实例
     */
    public static function setInstance($objInstance) {
        static::$objInstance = $objInstance;
    }

    /**
     * 将实例绑定到容器
     * @param string $strAbstract 类别名，类名，接口名
     * @param mix $mixInstance 实例
     */
    public function instance($strAbstract, $mixInstance) {
        $this->arrInstances[$strAbstract] = $mixInstance;
    }

    /**
     * 绑定共享服务到容器
     * @param  string  $strAbstract 类别名，实际类名，接口类名
     * @param  \Closure|string|null  $mixConcrete 类的构建闭包，实际类名
     */
    public function singleton($strAbstract, $mixConcrete = null) {
        $this->bind($strAbstract, $mixConcrete, true);
    }

    /**
     * 绑定服务到容器
     * @param  string  $strAbstract 类别名，实际类名，接口类名
     * @param  \Closure|string|null  $mixConcrete 类的构建闭包，实际类名
     * @param  bool  $blnShared 是否为共享服务
     */
    public function bind($strAbstract, $mixConcrete = null, $blnShared = false) {
        //如果没有给出具体类型，则将抽象类型设置为具体类型
        if (is_null($mixConcrete)) {
            $mixConcrete = $strAbstract;
        }

        //生成解析服务时需要的闭包
        if (!$mixConcrete instanceof Closure) {
            $mixConcrete = $this->getClosure($strAbstract, $mixConcrete);
        }

        //记录绑定关系
        $this->arrBindings[$strAbstract] = [
            'concrete' => $mixConcrete,
            'shared' => $blnShared
        ];
    }

    /**
     * 获取解析服务时需要的闭包
     * @param  string  $strAbstract
     * @param  string  $mixConcrete
     * @return \Closure
     */
    protected function getClosure($strAbstract, $mixConcrete) {
        return function ($objApp, $arrParameters = []) use ($strAbstract, $mixConcrete) {
            if ($strAbstract == $mixConcrete) {
                //可直接构建
                return $objApp->build($mixConcrete);
            }
            //还需要解析，如将接口绑定到接口的实现
            return $objApp->make($mixConcrete, $arrParameters);
        };
    }

    /**
     * 从容器中解析实例
     * @param string $strAbstract 类别名，实际类名，接口类名
     * @param array $arrParameters 解析实例需要的参数，如构造函数里的参数
     */
    public function make($strAbstract, $arrParameters = []) {
        //是否要重新解析实例
        $blnReBuild = !empty($arrParameters);

        //存在服务的共享实例，且不需要重建，直接返回共享实例
        if (isset($this->arrInstances[$strAbstract]) && !$blnReBuild) {
            return $this->arrInstances[$strAbstract];
        }

        //将解析参数放入参数堆栈
        $this->arrWith[] = $arrParameters;

        //获取抽象类型的具体类型
        $mixConcrete = $this->getConcrete($strAbstract);

        //构建实例
        $objObject = $this->build($mixConcrete);

        if ($this->isShared($strAbstract) && !$blnReBuild) {
            $this->arrInstances[$strAbstract] = $objObject;
        }

        //从参数堆栈移除参数
        array_pop($this->arrWith);

        //解析完成，返回服务的实例
        return $objObject;
    }

    /**
     * 获取抽象类型的具体类型
     * @param  string $strAbstract
     * @return mixed $mixConcrete
     */
    protected function getConcrete($strAbstract) {
        //从绑定关系获取
        if (isset($this->arrBindings[$strAbstract])) {
            return $this->arrBindings[$strAbstract]['concrete'];
        }

        //无绑定，返回抽象类型，如解析未绑定到容器的类
        return $strAbstract;
    }

    /**
     * 抽象类型是否为共享的
     * @param  string  $strAbstract
     * @return bool
     */
    protected function isShared($strAbstract) {
        return isset($this->arrInstances[$strAbstract]) ||
                (isset($this->arrBindings[$strAbstract]['shared']) && $this->arrBindings[$strAbstract]['shared'] === true);
    }

    /**
     * 构建实例
     * @param  mixed  $mixConcrete 具体类型
     */
    protected function build($mixConcrete) {
        //如果抽象类型为闭包函数，则直接执行
        if ($mixConcrete instanceof Closure) {
            return $mixConcrete($this, $this->getLastParameterOverride());
        }

        //获取类型的反射类
        $objReflector = new ReflectionClass($mixConcrete);

        //如果反射类不可实例化，则抛出异常
        if (!$objReflector->isInstantiable()) {
            return $this->notInstantiable($mixConcrete);
        }

        //将类型放入解析堆栈
        $this->arrBuildStack[] = $mixConcrete;

        //获取反射类的构造方法
        $objConstructor = $objReflector->getConstructor();

        if (is_null($objConstructor)) {
            //如果反射类没有构造方法
            //从解析堆栈中移除类型
            array_pop($this->arrBuildStack);
            //返回类型的实例
            return new $mixConcrete;
        }

        //获取反射类构造函数的参数
        $arrDependencies = $objConstructor->getParameters();

        //解析构造函数的依赖项
        $arrDepInstances = $this->resolveDependencies($arrDependencies);

        //从解析堆栈中移除类型
        array_pop($this->arrBuildStack);

        //使用构造函数的依赖参数来生成实例
        return $objReflector->newInstanceArgs($arrDepInstances);
    }

    /**
     * 从参数堆栈中获取解析需要的参数
     * @return array
     */
    protected function getLastParameterOverride() {
        $a = count($this->arrWith) ? end($this->arrWith) : [];
        return $a;
    }

    /**
     * 抛出不可实例化的异常
     * @param  string  $mixConcrete
     * @throws Exception
     */
    protected function notInstantiable($mixConcrete) {
        if (!empty($this->arrBuildStack)) {
            $strPrevious = implode(', ', $this->arrBuildStack);
            $strMessage = "[$mixConcrete]不可实例化，当在构建[$strPrevious]时";
        } else {
            $strMessage = "[$mixConcrete]不可实例化";
        }
        throw new ApplicationException($strMessage);
    }

    /**
     * 抛出不可解析的参数异常
     * @param  \ReflectionParameter  $objParameter
     * @return void
     *
     * @throws \Exception
     */
    protected function unresolvablePrimitive(ReflectionParameter $objParameter) {
        $strMessage = "类[{$objParameter->getDeclaringClass()->getName()}]中的参数[$objParameter->name]不能解析";

        throw new ApplicationException($strMessage);
    }

    /**
     * 解析构造函数的依赖项
     * @param  array  $arrDependencies
     * @return array
     */
    protected function resolveDependencies($arrDependencies) {
        $arrResults = [];
        foreach ($arrDependencies as $objDependency) {
            //如果解析参数中包含依赖项参数，则直接使用不需要解析
            if ($this->hasParameterOverride($objDependency)) {
                $arrResults[] = $this->getParameterOverride($objDependency);
                continue;
            }
            //解析简单类型或类类型
            $arrResults[] = is_null($objDependency->getClass()) ? $this->resolvePrimitive($objDependency) : $this->resolveClass($objDependency);
        }

        return $arrResults;
    }

    /**
     * 解析参数中是否有依赖项参数
     * @param  \ReflectionParameter  $objDependency
     * @return bool
     */
    protected function hasParameterOverride($objDependency) {
        return array_key_exists($objDependency->name, $this->getLastParameterOverride());
    }

    /**
     * 获取解析参数中的依赖项参数
     * @param  \ReflectionParameter  $objDependency
     * @return mixed
     */
    protected function getParameterOverride($objDependency) {
        return $this->getLastParameterOverride()[$objDependency->name];
    }

    /**
     * 解析简单类型
     * @param  \ReflectionParameter  $objParameter
     * @return mixed
     */
    protected function resolvePrimitive(ReflectionParameter $objParameter) {
        //参数有默认值，则使用默认值
        if ($objParameter->isDefaultValueAvailable()) {
            return $objParameter->getDefaultValue();
        }

        //抛出不可解析参数的异常
        $this->unresolvablePrimitive($objParameter);
    }

    /**
     * 解析类类型
     * @param  \ReflectionParameter  $objParameter
     * @return mixed
     * @throws \Exception
     */
    protected function resolveClass(ReflectionParameter $objParameter) {
        try {
            //return $this->make($objParameter->getClass()->name, $this->getLastParameterOverride());
            return $this->make($objParameter->getClass()->name);
        } catch (Exception $objException) {
            //解析失败，如果有默认值使用默认值
            if ($objParameter->isOptional()) {
                return $objParameter->getDefaultValue();
            }
            throw new ApplicationException($objException->getMessage());
        }
    }

}
