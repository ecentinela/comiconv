# output controller
Output = can.Control
    # email form is sent
    'form submit': ($el, e) ->
        # stop email submit
        e.preventDefault()

        # get the email
        email = $el.find('input').val()

        # remove the form
        @notice.remove()

        # store the email
        $.post $el.prop('action'), email: email

    # constructor
    init: ->
        # get elements
        @container = @element.find '.container'
        @notice = @element.find '#notice'
        @download = @element.find '#download'
        @downloadCheck = @download.find 'small'

        # prepare check
        @prepareCheck() if @downloadCheck.length

        # show container
        @container.removeClass 'hide'

    # prepare the conversion check
    prepareCheck: ->
        setTimeout =>
            $time = @downloadCheck.find 'strong'
            time = parseInt $time.text()

            if time == 0
                @check()
            else
                $time.text time - 1

                @prepareCheck()
        , 1000

    # check if conversion is done
    check: ->
        @downloadCheck.html ExposeTranslation.get 'output.checking'

        $.get location.href, (response) =>
            $download = $(response).find '#download'

            # conversion is not ready
            if $download.find('a').length
                # replace content
                @download.html $download.html()
            # conversion is complete
            else
                # set text again
                @downloadCheck.html(ExposeTranslation.get 'output.check', seconds: 10)

                # prepare check again
                @prepareCheck()

# initialize output controller on dom ready
$ -> new Output 'body'
