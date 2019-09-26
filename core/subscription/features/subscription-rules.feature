Feature: Subscription rules

  Rules:
  - Downgrade to lower package is not possible
  - Buying heartbeats available for standard and premium
  - Cannot buy demo twice

  Scenario: Upgrade demo to standard
    Given Tenant subscribed "demo" package
    When Tenant attempt to buy "standard" package
    Then Tenant should be upgraded to "standard" package

  Scenario: Upgrade demo to premium
    Given Tenant subscribed "demo" package
    When Tenant attempt to buy "premium" package
    Then Tenant should be upgraded to "premium" package

  Scenario: Upgrade standard to premium
    Given Tenant subscribed "standard" package
    When Tenant attempt to buy "premium" package
    Then Tenant should be upgraded to "premium" package

  Scenario: Buy another premium
    Given Tenant subscribed "premium" package
    When Tenant attempt to buy "premium" package
    Then Tenant should be upgraded to "premium" package

  Scenario: Downgrade premium to standard
    Given Tenant subscribed "premium" package
    When Tenant attempt to buy "standard" package
    Then Domain exception should occure
    