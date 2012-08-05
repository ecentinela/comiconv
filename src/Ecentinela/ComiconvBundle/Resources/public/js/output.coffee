# output controller
Output = can.Control
    # constructor
    init: ->
        # get elements
        @container = @element.find '.container'

        # create tooltips
        @createTooltips @element

        # show container
        @container.removeClass 'hide'

    # create tooltips
    createTooltips: ($node) ->
        $node.find('[rel=tooltip]').tooltip()

# initialize output controller on dom ready
$ -> new Output 'body'
