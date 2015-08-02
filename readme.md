# Widget Title HTML

Allows a limited set of inline HTML in widget titles.

## Allowed HTML elements

The following are the allowed HTML elements and their allowed attributes.

* `<a href="" title="">`
* `<abbr title="">`
* `<acronym title="">`
* `<code>`
* `<em>`
* `<strong>`
* `<i>`
* `<b>`

## FAQs

### Why doesn't this plugin doesn't work with X widget?

There's several reasons it might not work.

* The widget doesn't have a title field.
* The widget doesn't run the standard `widget_title` filter hook over the widget title.
* The widget title is saved as a different option than `title` (standard option name).
* The widget is doing something special that's impossible to account for.

These are things I have no control over.  I'll happily look at any widget and tell you why it's not working though.  We might be able to get your plugin/theme author to update their code in some cases.