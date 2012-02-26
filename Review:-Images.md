## Syntax for images

Images can be *inline* in the text, like:

    This is a paragraph with an embedded image ![alt text](http://test.com/data1/images/1.jpg "My optional title")

The rule here is the same as for inline links except that it bagin with an exclamation point. Then, between brackets, the alternative text (*displays if the image can't be loaded*) followed by, between parenthesis, the URL of the image, relative or asbolute, and an optional title wrapped in double-quotes.

Using this notation is the basis of the synta for images. But it can make the file not easy to read, which is the first goal of Markdown.

So we can use **references** for images. This allows us to keep the URL and other informations of the image outside of the content. For example:

    This is a paragraph with a referenced image ![alt text][imageid]. I can continue my content clearly because it is still readable for human eyes ...

    ![imageid]: http://test.com/data1/images/1.jpg "My optional title"

The image here in the final content will be exactly the same as above. The point is just that the informations are not in the content but after it.