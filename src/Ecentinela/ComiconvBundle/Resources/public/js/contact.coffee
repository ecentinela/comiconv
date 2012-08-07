# contact controller
Contact = can.Control
    # form submit
    'form submit': ($el, e) ->
        e.preventDefault()

        @button.prop 'disabled', 'disabled'

        $.post @form.prop('action'), email: @email.val(), text: @text.val(), =>
            @button.prop 'disabled', null
            @email.val ''
            @text.val ''
            @alert.addClass 'in'

    # check form validation
    'form input, form textarea keyup': ->
        valid = /^([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+/.test(@email.val()) and @text.val().length > 0
        @button.prop 'disabled', if valid then null else 'disabled'

    # hide alert
    '.alert a click': ($el, e) ->
        e.preventDefault()

        @alert.removeClass 'in'

    # constructor
    init: ->
        # get elements
        @container = @element.find '.container'
        @form = @container.find 'form'
        @email = @form.find 'input'
        @text = @form.find 'textarea'
        @button = @form.find 'button'
        @alert = @container.find '.alert'

        # show container
        @container.removeClass 'hide'

# initialize about controller on dom ready
$ -> new Contact 'body'
