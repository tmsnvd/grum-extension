<?php

namespace FinanceTechnology\GrumExtension;

use FinanceTechnology\GrumExtension\Task\BranchCommitCompare;
use GrumPHP\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ExtensionLoader.
 */
class ExtensionLoader implements ExtensionInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @return Definition
     */
    public function load(ContainerBuilder $container)
    {
        return $container->register('task.git_branch_commit_compare', BranchCommitCompare::class)
            ->addArgument(new Reference('config'))
            ->addArgument(new Reference('git.repository'))
            ->addTag('grumphp.task', ['config' => 'git_branch_commit_compare']);
    }
}
