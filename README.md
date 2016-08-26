# WPKit
> Object-oriented development framework for WordPress to simplify developers life.


Now you can spend less time for routine operations like creating post-types, taxonomies, meta-boxes. 
WPKit will help you write less code -> drink more beer :)

_Powered by Redink AS_

## License

The WPKit framework is open-source software and distributed under the GPL-2+ license. See [LICENSE](LICENSE) for more information.

## Initalization

Just include WPKit autoloader

```php
require_once __DIR__ . 'WPKit/init_autoloader.php';
```

and you can start using it. We recommend use module structure fully supported by WPKit.

## Usage example

### Post type

Lets create post type Cars. You just need create new instance of `PostType` with slug and single name in parameters.

```php
$cars_post_type = new WPKit\PostType\PostType('car','Car');
```

### Metabox

Now we need add some custom fields. First of all we need create metabox

```php
$metabox = new WPKit\PostType\MetaBox('data','Properties');
```

And then we need add this metabox to our post type

```php
$cars_post_type->add_metabox( $metabox );
```

or

```php
$metabox->add_post_type( $cars_post_type );
``` 
You can add one matabox to several post types. 

### Fields

```php
$metabox->add_field( 'reg_no', 'Registration #' );  // By default Text field will be used
$metabox->add_field( 'year', 'Year', 'Number' );    // You can set Field in 3rd parameter as string
$metabox->add_field( 'color', 'Color', function(){  // Or use more flexible callback function
	$filed = new WPKit\Fields\Select();
	$filed->set_options([                           // Like settings options and other
		'red',
		'black',
		'white',
		'yellow'
	]);
	
	return $filed;                                  // Function should always return created filed
} );

```

![](https://s3-eu-west-1.amazonaws.com/static-redink/wpkit/example.png)

To get value of custom fields use `MetaBox::get()` method.

```php
<?php $year = WPKit\PostType\MetaBox::get( get_the_ID(), 'data', 'year' ); ?>
```



More features will come in Wiki or just explore code ;)

## Release History

Version 1.6.2

 + Default values for repeatable metabox
 + Auto add 'http://' in Url field
 + Default value in Option::get method
 + Remove srcset from image in Image field
 * Composer rename
 * Fixes

Version: 1.6

 + PostLoader for huge homepages builders to reduce SQL queries
 + MetaBox Related Posts limit parameter added
 + PostType show_in_menu, public parameters added
 + MetaBoxRepeatable - added vertical layout and some improvements
 + Taxonomy show_ui parameter added
 * PostType has_archive type fix
 * Video field changed to use oEmbed
 * File field fix for SVG support
 * WP 4.5 improvements
 * TaxonomyMeta deprecated due to core WP functionality
 * PHP 7 preparation

Version: 1.5.6
 
 * Youtube API key fix 

Version: 1.5.5

 + APSIS integration
 * Fixes on youtube and instagram integrations
 * PHPDoc description added

Version: 1.5

 + WPEditor params added
 + Additional request parameters in Instagram
 + Added add_action add_filter into autoread Initialization
 + Add Transient cache
 + Added JS triggers on repeatable actions
 * Fixes in repeatable metabox
 * Fix for multiple image/file buttons on screen

Version: 1.3

 + Added rewrite for post types
 + Added select2 for select field
 + Added localizations
 + Added limits for metabox Repeatable
 + Added Users meta box
 + Implemented Youtube integration class
 + Implemented Instagram integration class
 + Reload JS on fields
 + Object cache support
 * More table bulk action fix
 * Repeatable metabox save fixes

Version: 1.2.1

 + Added rewrite options for post type
 * Table bulk action fix
 * AbstractWidget AJAX fix


Version: 1.2

 + Context to metaboxes
 + Multiple select support
 + YouTube integration class
 + MetaBox Related Posts
 * Wrong url in enqueue inline script
 * No results message for Map field

Version: 1.1

 + Added Fields
    + Date
    + File
    + Map
    + WPEditor
    + Hidden
    + Number
 + Repeatable/Sortable fields set
 * Table order fix

Version: 1.0.4

 + first version
