#language: en
@full

Feature: Make transaction
As a user I need to make a transaction using Pix and credit card

Scenario: Transaction with credit card 
  Given I request a transaction
  When I sent the transaction data
  Then the transaction has success

Scenario: Let the credit card data field blank and try to pay anyway
  Given I request a transaction without card token
  When I send the transaction 
  Then the transaction cannot be approved

Scenario: Pix transaction
  Given I request a Pix transaction
  When I input all the transaction information
  Then the pix transaction is approved
