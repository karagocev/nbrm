# Nbrm

Nbrm is a small PHP library used to retrieve current and historical exchange rates from the web service of the National Bank of the Republic of Macedonia ([NBRM](http://nbrm.mk/)).

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Basic usage](#basic-usage)
    - [Historical exchange rates](#historical-exchange-rates)
    - [Raw data](#raw-data)
- [License](#license)

## Requirements

* PHP >= 5.6
* ext-soap

## Installation

### Using Composer (recommended)

Once you've installed and initialized [Composer](https://github.com/composer/composer), simply add this package as a dependency to your own project:

```
composer require karagocev/nbrm
```

If you haven't done so already, you'll also need to import Composer's autoloader:

```
require 'vendor/autoload.php';
```

### Manually

Download the latest version of the library and import it in your project:

```
require 'path/to/src/Nbrm.php';
```

## Usage

### Basic usage

```
use Nbrm\Nbrm;

$nbrm = new Nbrm();
$rates = $nbrm->getRates();
```

### Historical exchange rates

The class constructor takes two optional parameters which are used to obtain historical exchange rates. Both of these parameters can be just about any English textual datetime description, a UNIX timestamp, or a combination of both:

```
use Nbrm\Nbrm;

$nbrm = new Nbrm(1493683200, 'May 3 2017');
$rates = $nbrm->getRates();
```

| Parameter | Type   | Description |
|:--------- |:------ |:----------- |
| startDate | string | The beginning of the date range |
| endDate   | string | The end of the date range |

If you need to retrieve the exchange rates for a specific date, simply enter the same value for both parameters, like so:

```
$nbrm = new Nbrm('2017-05-01', '2017-05-01');
```

##### Notes

- If either of the parameters is omitted, the web service will take the remaining date as the starting point and return the historical exchange rates between it and the current date.
- If you've entered a date which cannot be readily parsed by DateTime, it will be silently substituted with the current date.

##### Example output

The result is an opionionated multidimensional array with the following structure:

```
Array
(
    [USD] => Array
        (
            [number] => 840
            [name] => US dollar
            [country] => USA
            [mkName] => САД долар
            [mkCountry] => С А Д
            [rates] => Array
                (
                    [2017-05-02] => 56.3732
                    [2017-05-03] => 56.4763
                )

        )

    ...
)
```

##### Return values

| Parameter | Type    | Description |
|:--------- |:------- |:----------- |
| number    | integer | ISO 4217 currency number |
| name      | string  | Currency name in English |
| country   | string  | Currency issuing country (or entity) in English |
| mkName    | string  | Currency name in Macedonian |
| mkCountry | string  | Currency issuing country (or entity) in Macedonian |
| rates     | array   | Array of historical exchange rates, ordered by date |

The *rates* array is composed of date => price pairs:

| Parameter | Type   | Description |
|:--------- |:------ |:----------- |
| date      | string | ISO 8601 date string |
| price     | double | The value of one unit of foreign currency in Macedonian denari |

### Raw data

If for whatever reason you need to access the raw response data returned by the web service, you can do so by calling the public method getResponse:

```
$response = $nbrm->getResponse();
```

## License

NBRM is licensed under the [MIT License](LICENSE).