<?php

namespace helper;

use Phalcon\Di\Injectable;
use Phalcon\Translate\Adapter\NativeArray;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;

class locale extends Injectable
{
    /**
     * @return NativeArray
     */
    public function getTranslator(): NativeArray
    {
        // Ask browser what is the best language
        if (null!==($this->request->get('lang'))) {
            $language = $this->request->get('lang');
        } else {
            $language = 'en';
        }
        
        $messages = [];
        $translationFile = APP_PATH.'/messages/' . $language . '.php';
        
        
        if (true !== file_exists($translationFile)) {
            $translationFile = APP_PATH.'/messages/' . $language . '.php';
        }
        
        require $translationFile;

        $interpolator = new InterpolatorFactory();
        $factory      = new TranslateFactory($interpolator);
        
        return $factory->newInstance(
            'array',
            [
                'content' => $messages,
            ]
        );
    }
}