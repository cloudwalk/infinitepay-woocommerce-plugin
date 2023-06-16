#language: en
@full

Feature: Create token to tokenize a card
As a user I want to create a token to tokenize my credit card


Scenario: Create a token JWT to tokenize credit card
  Given the API address to create a token JWT
  When I send a request to create it
  Then the I have success to tokenize the card
