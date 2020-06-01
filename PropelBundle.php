<?php

/**
 * This file is part of the PropelBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */
namespace Propel\Bundle\PropelBundle;

use Propel\Bundle\PropelBundle\DependencyInjection\Security\UserProvider\PropelFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * PropelBundle.
 *
 * @author William DURAND <william.durand1@gmail.com>
 */
class PropelBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        require_once $this->container->getParameter('propel.path').'/runtime/lib/Propel.php';

        // kernel.root_dir` and `Kernel::getRootDir() are deprecated since SF 4.2
        $projectDir = $this->container->hasParameter('kernel.project_dir')
            ? ($this->container->getParameter('kernel.project_dir'))
            : ($this->container->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'..'.PATH_SEPARATOR);

        if (0 === strncasecmp(PHP_SAPI, 'cli', 3)) {
            set_include_path(
                $projectDir.PATH_SEPARATOR.
                $this->container->getParameter('propel.phing_path').PATH_SEPARATOR.
                $this->container->getParameter('propel.phing_path').DIRECTORY_SEPARATOR.'classes'.PATH_SEPARATOR.
                get_include_path()
            );
        }

        if (!\Propel::isInit()) {
            \Propel::setConfiguration($this->container->get('propel.configuration'));

            if ($this->container->getParameter('propel.logging')) {
                $config = $this->container->get('propel.configuration');
                $config->setParameter(
                    'debugpdo.logging.methods',
                    array(
                        'PropelPDO::exec',
                        'PropelPDO::query',
                        'PropelPDO::prepare',
                        'DebugPDOStatement::execute',
                    ),
                    false
                );
                $config->setParameter(
                    'debugpdo.logging.details',
                    array(
                        'time' => array('enabled' => true),
                        'mem' => array('enabled' => true),
                        'connection' => array('enabled' => true),
                    )
                );

                \Propel::setLogger($this->container->get('propel.logger'));
            }

            \Propel::initialize();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if ($container->hasExtension('security')) {
            $container->getExtension('security')->addUserProviderFactory(
                new PropelFactory('propel', 'propel.security.user.provider')
            );
        }
    }
}
