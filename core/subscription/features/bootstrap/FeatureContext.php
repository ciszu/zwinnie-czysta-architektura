<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Mozartify\Subscription\RamSubscriptionRepository;
use Mozartify\Subscription\SubscriptionDomain;
use Mozartify\Subscription\PardotEcommerceAdapter;
use Mozartify\Subscription\SubscriptionDomainException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * @var SubscriptionDomain
     */
    private $subscriptionDomain;

    /**
     * @var int
     */
    private $subscriptionId;

    /**
     * @var SubscriptionDomainException
     */
    private $domainException;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->subscriptionDomain = new SubscriptionDomain(
            new RamSubscriptionRepository(),
            new PardotEcommerceAdapter())
        ;
    }

    /**
     * @Given Tenant subscribed :arg1 package
     */
    public function tenantSubscribedPackage($arg1)
    {
        $this->subscriptionId = $this->subscriptionDomain->subscribe('acme', $arg1);
    }

    /**
     * @When Tenant attempt to buy :arg1 package
     */
    public function tenantAttemptToBuyPackage($arg1)
    {
        try {
            $this->subscriptionDomain->buyPackage($this->subscriptionId, $arg1);
        } catch (SubscriptionDomainException $e) {
            $this->domainException = $e;
        }
    }

    /**
     * @Then Tenant should be upgraded to :arg1 package
     */
    public function tenantShouldBeUpgradedToPackage($arg1)
    {
        $packageInfo = $this->subscriptionDomain->getActivePackage($this->subscriptionId);
        \PHPUnit\Framework\Assert::assertSame(
            $packageInfo['type'],
            $arg1
        );
    }

    /**
     * @Then Domain exception should occure
     */
    public function domainExceptionShouldOccure()
    {
        \PHPUnit\Framework\Assert::assertIsObject($this->domainException);
    }
}
