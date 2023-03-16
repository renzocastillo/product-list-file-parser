# Build a Supplier Product List Processor

### Requirement: 

We have multiple different formats of files that need to be parsed and returned back as a Product object with all the headings as mapped properties. 

Each product object constitutes a single row within the csv file.

### Application usage:

#### Example command:
`php parser.php --file examples/products_comma_separated.csv --unique-combinations=examples/combination_count.csv -m -t`

When the above is run the parser should display row by row each product object representation of the row. And create a file with a grouped count for each unique combination i.e. make, model, colour, capacity, network, grade, condition.

#### Command options
 * `-f` ` --file` *(required)*: The file path which has your input product list
 * `-u` `--unique-combinations` *(required)*: The file path where to output the combinations 
 * `-m` `--memory` : Show the memory usage after the command has run
 * `-t` `--time`: Shows the execution time after the command has run

### Application Testing

PHPUnit library package has been added into composer.json to perform the unit testings

`./vendor/bin/phpunit tests/Unit/ProductTest.php`

When the above is run the application executes a Product Model Tests and the terminal should output the result.

### Project structure

```
project
└─ app
    └─── Controllers : ParserController
    └─── Models : Product Model
└─ examples: Example files 
└─ tests
    └─Unit: Unit tests
```