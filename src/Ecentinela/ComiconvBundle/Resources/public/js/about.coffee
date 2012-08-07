# about controller
About = can.Control
    # constructor
    init: ->
        # get elements
        @container = @element.find '.container'

        # show container
        @container.removeClass 'hide'

# initialize about controller on dom ready
$ -> new About 'body'
