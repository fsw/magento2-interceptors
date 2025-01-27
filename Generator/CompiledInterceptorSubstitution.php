<?php

namespace Creatuity\Interception\Generator;

use Magento\Setup\Module\Di\Compiler\Config\Chain\InterceptorSubstitution;
use Magento\Setup\Module\Di\Compiler\Config\ModificationInterface;

/**
 * Class CompiledInterceptorSubstitution adds required parameters to interceptor constructor
 */
class CompiledInterceptorSubstitution implements ModificationInterface
{
    /**
     * @var InterceptorSubstitution
     */
    private $interceptorSubstitution;

    /**
     * @param InterceptorSubstitution $interceptorSubstitution
     */
    public function __construct(InterceptorSubstitution $interceptorSubstitution)
    {
        $this->interceptorSubstitution = $interceptorSubstitution;
    }

    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     */
    public function modify(array $config)
    {
        $config = $this->interceptorSubstitution->modify($config);
        foreach ($config['arguments'] as $instanceName => &$arguments) {
            $finalInstance = isset($config['instanceTypes'][$instanceName]) ? $config['instanceTypes'][$instanceName] : $instanceName;
            if (substr($finalInstance, -12) === '\Interceptor') {
                foreach (CompiledInterceptor::propertiesToInjectToConstructor() as  $type => $name) {
                    $preference = isset($config['preferences'][$type]) ? $config['preferences'][$type] : $type;
                    foreach ($arguments as $argument) {
                        if (isset($argument['_i_']) && $argument['_i_'] == $preference) {
                            continue 2;
                        }
                    }
                    $arguments = array_merge([$name => ['_i_' => $preference]], $arguments);
                }

            }
        }

        return $config;
    }
}
