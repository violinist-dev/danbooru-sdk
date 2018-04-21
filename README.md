# Danbooru PHP SDK

[![Build status](https://api.travis-ci.org/desu-project/chanbooru-interface.svg)](https://travis-ci.org/desu-project/danbooru-sdk)

It's collection of entities, value objects and simple client to work with [Danbooru API](https://danbooru.donmai.us/wiki_pages/43568) in read-only mode. This SDK implements [desu-project/chanbooru-interface](https://github.com/desu-project/chanbooru-interface).

## Installation

````
composer require desu-project/danbooru-sdk
````

## Getting started

````php
require 'vendor/autoload.php';

use DesuProject\DanbooruSdk\Client;
use DesuProject\DanbooruSdk\Post;

$client = new Client(
    'abc', // api key
    false // false = use Danbooru instead of Safebooru
);

$posts = Post::search(
    $client, // Client object
    ['animal_ears'], // array of tags
    1, // page number
    30 // posts per page
);

foreach ($posts as $post) {
    echo $post->getId() . '<br>' . "\n";
}
````

For details see sources. They are well documented.

## License

Danbooru SDK is licensed under MIT license. For further details see [LICENSE](LICENSE) file.
