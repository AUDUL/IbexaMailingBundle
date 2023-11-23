<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaMailingBundle\DependencyInjection;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends SiteAccessAware\Configuration
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ibexamailing');
        $rootNode = $treeBuilder->getRootNode();
        $systemNode = $this->generateScopeBaseNode($rootNode);

        $systemNode
                ->scalarNode('email_subject_prefix')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('email_from_address')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('email_from_name')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('email_return_path')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('simple_mailer')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('mailing_mailer')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('default_mailinglist_id')->isRequired()->cannotBeEmpty()->end()
                ->booleanNode('unsubscribe_all')->isRequired()->end()
                ->booleanNode('delete_user')->isRequired()->end();

        return $treeBuilder;
    }
}
