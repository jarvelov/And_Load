And load
==============

And load is a Wordpress plugin to easily load styles and scripts by using a shortcode. And load lets you create and upload JavaScript and CSS files which then can be loaded whenever with the `[and_load]` shortcode. The files are loaded by the [`wp_enqueue_script`](https://codex.wordpress.org/Function_Reference/wp_enqueue_script) and [`wp_enqueue_style`](https://codex.wordpress.org/Function_Reference/wp_enqueue_style) functions in Wordpress.

And load has support to not only load script and style files but it can also output additional data onto the page directly with its `data` parameter. The data can optionally be wrapped inside `<script>` or `<style>` tags for additional customization.

CSS and JavaScript files are automatically compressed (minified) when loaded and saves every update you make to a new revision of the file so you can easily revert back to a working copy of a file. The plugin includes the [Ace editor](http://ace.c9.io/) with over 20 themes to choose from, syntax highlighting and much more, all to make editing files more fun.

##Install

Download directly from the Wordpress plugins repository.

or

Clone the repository with Git into the wp-content/plugins/ directory:

`git clone https://github.com/jarvelov/And_Load.git`

##Usage

**`id`**  
To load a file simply specify its ID:
`[and_load id="2"]`

Load multiple files by comma separating the ids
`[and_load id="2,3,4,18"]`

**`revision_override`**  
And Load automatically loads the most recent revision of a saved file. If you want to load a specific revision of a file you can override it with this setting. Here the 14th revision of the file with ID 2 is loaded instead of the most recent one.
`[and_load id="2" revision_override="14"]`

**`jquery_override`**  
By default `jQuery` is loaded with each script file for your scripting please. If you turn this off globaly you can still enable it for specific files.
`[and_load id="2" jquery_override="true"]`

**`minify_override`**  
Since And Load autominifies all files it can be somewhat of a problem to debug. To load the unminified version of a file instead use:
`[and_load id="2" minify_override="true"]`

**`data`**  
Dump additional data to the page before the files are loaded. Very useful in combination with the `data_wrap` parameter if you need to define a variable before a script is loaded or you want to add a CSS class dynamically. 
`[and_load id="2" data="This text will be dumped to the page"]`

**`data_wrap`**  
Wrap the content in the `data` parameter withing `<script>` or `<style>` tags before it is dumped to the page.

`[and_load id="2" data=".customClass{ background-color: red; }" data_wrap="style"]`

##Examples

###Example 1
Say that you have a JavaScript file that looks like this which has ID 3:

```javascript
  myFunction(myVariable) {
    alert(myVariable);
  }
```

Now we can use And Load to call this function from the shortcode by using the `data` and `data_wrap` parameter like so:

`[and_load id="3" data="var myVariable='Hello!';myFunction(myVariable);" data_wrap="script"]`

Now when the page is loaded we will be greeted with an alert box with the value of myVariable, which in this case is "Hello!".

Pretty sweet huh?

###Example 2

Okay, so we have a boring HTML table on our page and we want to freshen it up with some color and nice functions. 

```html
<table id="and_load_example">
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>1</td>
      <td>First</td>
    </tr>
    <tr>
      <td>2</td>
      <td>Second</td>
    </tr>
    <tr>
      <td>3</td>
      <td>Third</td>
    </tr>
  </tbody>
</table>
```

We create a new CSS file with the following content:

```css
#and_load_example {
  border: 3px double #FEFEFE;
  padding: 20px;
  width: 500px;
  text-align: center;
}

#and_load_example > tbody > tr:nth-child(odd) > td, 
#and_load_example > tbody > tr:nth-child(odd) > th {
   background-color: #F3F3F3;
}
```

Lets say the file was given ID 4. Now we load this file using the shortcode:
`[and_load id="4"]`

And our boring table now looks a bit more... fabulous maybe? Looks better though right?

###Example 3

A more advanced example. Here we utilize the fantastic [datatables](https://www.datatables.net/) library to make the table in our last example a bit more functional.

Datatables depend on jQuery so make sure the **Load jQuery with script files** setting is enabled or add `jquery_override="true"` to the shortcode if you have disabled the global setting.

Now let's download the JavaScript and CSS files. In this example I will be using version 1.10.7. Direct links found below:

[Datatables JavaScript](http://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js)

[Datatables CSS](http://cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css)

Upload them or copy and paste the content into the editor and save them (separately, remember that the File Type must be correct when saving). Lets say the JavaScript file was given ID 17 and CSS file ID 18. Going back to our page with the (if you followed example #2 then not quite so) ugly table we add our shortcode, which looks something like this:

`[and_load id="17,18" data="jQuery(document).ready(function() { jQuery('#and_load_example').dataTable(); });" data_wrap="script"]`

Then reload the page and ta-da! We now have a sortable table with all the goodies that comes with using the datatables library.

Okay, this is sweet already but now we can see the true potential of And Load. Lets go back to the editor and create a new JavaScript file with the following content.

```javascript
jQuery(document).ready(function() {
  if(typeof(tableId) != 'undefined') {
    jQuery(tableId).dataTable();
  }
});
```

Lets say this file was given ID 19
And lets modify our shortcode so it instead looks like this:

`[and_load id="17,18,19" data="var tableId='#and_load_example';" data_wrap="script"]`

What did we just do? We created a function to use the datatables library so that we can pass a variable to it instead of hardcoding it into a script. Now we can pass any DOM element's id or class attribute to the initialize a datatable on any table we want! Now isn't that neat?

Enough examples for now. Go play around with And Load yourself and see what you can make of it.

##License
Licensed under GPLv3+

##Contact
Contact me if you have any questions: tobias@jarvelov.se. If you want to help with the development of the plugin you are free to send a pull request.
