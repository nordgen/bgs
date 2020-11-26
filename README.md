# Barley Genetic Stock

[![GitHub license](https://img.shields.io/github/license/nordgen/bgs.svg)](https://raw.githubusercontent.com/nordgen/db-batch/master/LICENSE)

> ### Web application that managing database of Barley Genes and Barley Genetic Stocks.

This application should be able to be modified to handle other collections of genes from other crops, with some modifications. Something that could come in future versions, if the desicion of the software to be more generic and configurable.

----------

# Getting started

## Installation

Please check the official installation guide for server requirements before you start. 


Clone the repository

    git clone git@github.com:nordgen/bgs.git

Switch to the repo folder

    cd bgs

Install all the dependencies using composer

    composer install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env

Edit .env with your favorit text editing application, to set necessary information.
If another database will be used, enter that information here.  

    vim .env
    
Start the local development server

    docker-compose up -d

You can now access the server at http://localhost:8000

TODO: write instuction how to insert data from a database dump.

**TL;DR command list**

    git clone git@github.com:gothinkster/laravel-realworld-example-app.git
    cd laravel-realworld-example-app
    composer install
    cp .env.example .env
    vim .env
    docker-compose up -d 
    
**Make sure you set the correct database connection information before running the migrations** [Environment variables](#environment-variables)

    <TODO: write code to migrate database>
    <TODO: write code to inport database dump>


----------

# Code overview

## Dependencies

- [db-batch](https://github.com/nordgen/db-batch) - For database connection abstaction. DB Adapter class.
- [laminas-db](https://packagist.org/packages/laminas/laminas-db) - For database connection.
- [laminas-config](https://packagist.org/packages/laminas/laminas-config) - For simplifying config management.
- [phpspreadsheet](https://packagist.org/packages/phpoffice/phpspreadsheet) - For generating spreadsheats.
- [knp-snappy](https://packagist.org/packages/knplabs/knp-snappy) - For generating pdf.
- [wkhtmltopdf-amd64](https://packagist.org/packages/h4cc/wkhtmltopdf-amd64) - For generating pdf.
- [wkhtmltoimage-amd64](https://packagist.org/packages/h4cc/wkhtmltoimage-amd64) - For generating pdf.


## Folders

- `admin` - Contains php code for user administration
- `doc` - Folder for more mark down documents that atre referred from README.md 
- `images` - Contains the images of the application
- `jquery-ui-1.11.4.custom` - Contains the javascript library
- `script` - Contains the projects own javascript
- `src` - Contains classes of the project
- `style` - Contains css
- `system` - Contains reusable files like configuration, database connection, login, export to excell or pdf.
- `system/pdf` - Contains template file for pdf generation.




## Environment variables

- `.env` - Environment variables can be set in this file

***Note*** : You can quickly set the database information and other variables in this file and have the application fully working.

----------


**License**
This application is licensed under [GPLv3](doc/gpl-3.0.md)
