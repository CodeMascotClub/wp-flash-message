# CodeMascot WP Flash Message

A *PHP* *Composer* library for *WordPress* to make front-end flash messaging easy.

## Table Of Contents

* [Coding styles and technique](#coding-styles-and-technique)
* [Installation](#installation)
* [Usage](#usage)
* [Crafted by Khan](#crafted-by-khan)
* [License](#license)
* [Contributing](#contributing)

## Coding styles and technique
* All input data escaped and validated.
* **PSR-4** autoloading used.
* Developed as *Composer* package.
* **YODA** condition check applied.
* Maintained ***Right Margin*** carefully. Usually that is 80 characters.
* Used `true`, `false` and `null` in stead of `TRUE`, `FALSE` and `NULL`.
* **INDENTATION:** *TABS* has been used in stead of *SPACES*.
* *PHP Codesniffer* checked.
* *WordPress VIP* coding standard followed mostly.

## Installation

The best way to use this package is through Composer:

```BASH
$ composer require codemascot/wp-flash-message
```

## Usage

#### Step 1
Instantiate the `FlashMessage` class object like below-
```php
$flash_message = new \CodeMascot\WPFlashMessage\FlashMessage();
```
#### Step 2
Turn on the `SESSION` in *PHP* like below-
```php
$flash_message->start_session();
```
#### Step 3
Set you message like below-
```php
$flash_message->error(
	__(
		'Your message here',
		'text-domain'
	),
	// This is the URL where you want to redirect.
	home_url()
);
```
#### Step 4
Display the `SESSION` message like below-
```php
// Display the messages
$flash_message->display();
```
#### Step 5
Write `CSS` style as you want to style the message.

## Crafted by Khan

I'm Khan AKA *CodeMascot* a professional web developer and I've written this script for my personal usage.

## License

Copyright (c) 2018 Khan M Rashedun-Naby, CodeMascot

Good news, this plugin is free for everyone! Since it's released under the [MIT License](LICENSE) you can use it free of charge on your personal or commercial website.

## Contributing

All feedback / bug reports / pull requests are welcome.
