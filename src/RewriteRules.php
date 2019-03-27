<?php
/**
 * @see       https://github.com/laminas/laminas-zendframework-bridge for the canonical source repository
 * @copyright Copyright (c) 2019 Laminas Foundation (https://getlaminas.org)
 * @license   https://github.com/laminas/laminas-zendframework-bridge/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ZendFrameworkBridge;

class RewriteRules
{
    /**
     * @return array
     */
    public static function classRewrite()
    {
        // @todo Need to determine the final name and namespace for zf-console
        return [
            // Expressive
            'Zend\\ProblemDetails\\' => 'Expressive\\',
            'Zend\\Expressive\\' => 'Expressive\\',
            // Laminas
            'Zend\\' => 'Laminas\\',
            'ZF\\ComposerAutoloading\\' => 'Laminas\\ComposerAutoloading\\',
            'ZF\\Deploy\\' => 'Laminas\\Deploy\\',
            'ZF\\DevelopmentMode\\' => 'Laminas\\DevelopmentMode\\',
            // Apigility
            'ZF\\Apigility\\' => 'Apigility\\',
            'ZF\\' => 'Apigility\\',
            // ZendXml, API wrappers, zend-http OAuth support, zend-diagnostics
            'ZendXml\\' => 'Laminas\\Xml\\',
            'ZendService\\' => 'Laminas\\',
            'ZendOAuth\\' => 'Laminas\\OAuth\\',
            'ZendDiagnostics\\' => 'Laminas\\Diagnostics\\',
        ];
    }
}