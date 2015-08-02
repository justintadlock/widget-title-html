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

### Why does the HTML disappear with some WP widgets?

Core WordPress has 4 widgets that strip all HTML tags before outputting in the title field in the form.  I've created a ticket to address this: https://core.trac.wordpress.org/ticket/33235

The 4 widgets are:

* Archives
* Calendar
* Meta
* Text

HTML will save fine and be output on the front end.  However, until that can be addressed in core, you'll need to re-add the HTML any time you save the widget.

### Why doesn't this plugin work with X widget?

There's several reasons it might not work.

* The widget doesn't have a title field.
* The widget doesn't run the standard `widget_title` filter hook over the widget title.
* The widget title is saved as a different option than `title` (standard option name).
* The widget is doing something special that's impossible to account for.

These are things I have no control over.  I'll happily look at any widget and tell you why it's not working though.  We might be able to get your plugin/theme author to update their code in some cases.

### RSS feed widget issues?

This particular core widget outputs its own HTML.  Links aren't allowed here.  I'm working on allowing all HTML but links for this widget.  It's on my to-do list.