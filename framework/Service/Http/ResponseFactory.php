<?php

namespace Framework\Service\Http;

/**
 * 响应工厂
 */
class ResponseFactory {

    /**
     * 生成响应实例
     */
    public function make($mixResponse) {
        if (is_array($mixResponse)) {
            //todo:目前没考虑header
            return new JsonResponse($mixResponse);
        } else if (is_file($mixResponse)) {
            return new FileResponse($mixResponse);
        } else {
            if (strpos($mixResponse, 'http') === 0) {
                //匹配方法待更新
                return new RedirectResponse($mixResponse);
            } else {
                return new HtmlResponse($mixResponse);
            }
        }
    }

}
