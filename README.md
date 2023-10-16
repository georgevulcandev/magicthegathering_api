# Magic The Gathering API

Based on the requirements provided I created an API that will allow to search cards and add cards to a deck.
The API specs can be found on openapi/schema.json file
### Setup project

Project local development env based on [Laradock](https://laradock.io/)

Set up project:
1. Clone project:
```bash
git clone https://github.com/georgevulcandev/magicthegathering_api 
```
2. Create databases
```bash
create database magicthegathering;
create database magicthegathering_test;
```
3. Import cards from third party API https://api.magicthegathering.io/v1/cards

This command will make http request to the 3rd party API and retrieve cards page by page.
For now it's set to start at page 815 until page 821, but can be modified to download all pages.
```bash
php artisan app:retrieve-cards
```
4. Run tests
```bash
php artisan test
```
