<?php

namespace FinanceTechnology\GrumExtension\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Util\Regex;
use GrumPHP\Configuration\GrumPHP;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Gitonomy\Git\Repository;

/**
 * Class BranchCommitCompare.
 */
class BranchCommitCompare implements TaskInterface
{
    /**
     * @var GrumPHP
     */
    protected $grumPHP;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * BranchCommitName constructor.
     *
     * @param GrumPHP    $grumPHP
     * @param Repository $repository
     */
    public function __construct(GrumPHP $grumPHP, Repository $repository)
    {
        $this->grumPHP = $grumPHP;
        $this->repository = $repository;
    }

    /**
     * @param ContextInterface $context
     *
     * @return bool
     */
    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitCommitMsgContext;
    }

    /**
     * @param ContextInterface $context
     *
     * @return TaskResultInterface
     */
    public function run(ContextInterface $context): TaskResultInterface
    {
        $config = $this->getConfiguration();
        $commitMessage = $context->getCommitMessage();
        $branchName = trim($this->repository->run('symbolic-ref', ['HEAD', '--short']));

        foreach ($config['matchers'] as $rule) {
            $regex = new Regex($rule);
            if (preg_match((string) $regex, $commitMessage, $matches)) {
                $branchName = explode('_', $branchName);
                if (isset($branchName[1], $matches[1]) && $matches[1] === '#'.$branchName[1]) {
                    return TaskResult::createPassed($this, $context);
                }
            }
        }

        $errors[] = sprintf('Commit message Redmine task number does not match branch name number %s != #%s', $matches[1], $branchName[1]);

        return TaskResult::createFailed($this, $context, implode(PHP_EOL, $errors));
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        $configured = $this->grumPHP->getTaskConfiguration($this->getName());

        return $this->getConfigurableOptions()->resolve($configured);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'git_branch_commit_compare';
    }

    /**
     * @return OptionsResolver
     */
    public function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'matchers' => ['(\#\d{5})'],
        ]);

        $resolver->addAllowedTypes('matchers', ['array']);

        return $resolver;
    }
}
