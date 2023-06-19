#language: en
@full
@credential
Feature: Create user credential 

Scenario: Create valid credential for an user who has E-commerce product active
  Given I am an user that wants to create credential
  When I send request to create my credential
  Then I have success on this flow


Scenario: Try to create valid credential for an user who does not have E-commerce product active 
  Given I am an user without E-commerce product
  When I send request to create my credential without this permission
  Then the request failed 