# input controller
Input = can.Control
    # drag the tr
    '.icon-move draginit': ($el, e, drag) ->
        $node = $el.closest 'tr'
        file = $node.data 'file'

        $el.data 'node', $node

        drag.ghost().append "<span class='label label-info' style='margin-left:15px'>#{file.name}</span>"

        drag.limit @tbody

    # dropped the element
    'tr dropon': ($el, e, drop, drag) ->
        console.log drag
        console.log drag.element
        $node = drag.element.data 'node'
        $node.insertBefore $el

    # click on the trash icon to remove a file
    '.icon-trash click': ($el, e) ->
        # hide the tooltip manually
        $el.tooltip 'hide'

        # remove the file
        @removeFile $el.closest('tr').data('file')

    # upload the files
    '.btn-primary click': ->
        @uploader.start()

    # constructor
    init: ->
        # get elements
        @container = @element.find '.container'
        @uploaderError = @element.find '#requirement-error.hide'
        @drop = @element.find '#drop'
        @table = @element.find '#table'
        @tbody = @table.find 'tbody'
        @sample = @tbody.find 'tr'

        @sample.remove()

        # create tooltips
        @createTooltips @element

        # prepare uploader
        @prepareUploader()

    # create tooltips
    createTooltips: ($node) ->
        $node.find('[rel=tooltip]').tooltip()

    # prepare the uploader
    prepareUploader: ->
        # detect file upload is available
        if $('<input type="file" />').prop 'disabled'
            # show upload error
            @uploaderError.removeClass 'hide'
        else
            # show container
            @container.removeClass 'hide'

            # events on drag & drop
            @element.on 'dragenter', =>
                console.log 'enter'
            .on 'dragexit', =>
                console.log 'exit'
            .on 'drop', =>
                console.log 'drop'

            # prepare upload
            @uploader = new plupload.Uploader
                runtimes: 'html5,flash'
                browse_button : 'upload-link'
                drop_element: 'body'
                url: Routing.generate 'input'
                flash_swf_url: '/bundles/ecentinelacomiconv/Resources/public/plupload/js/plupload.flash.swf'
                filters: [
                    title: 'Comic files (jpg, pdf, cbz)'
                    extensions: 'jpg,pdf,cbz'
                ]
                init:
                    FilesAdded: (up, files) =>
                        @addFile file for file in files

                    UploadProgress: (up, file) =>
                        console.log 'progress'
                        # set progress
                        #@progress.height file.loaded * @progress.width() / file.size

                    FileUploaded: (up, file, response) =>
                        console.log 'uploaded'
                        # hide progress
                        #@progress.height 0

                        # set image and store it on image input
                        #@imageImg.prop 'src', response.response
                        #@image.val response.response

                    Error: (up, response) =>
                        console.log 'error'
                        # hide progress
                        #@progress.height 0

                        # show error
                        #@showError ExposeTranslation.get('views.account.register.upload_error')

            @uploader.init()

    # get the node for the given file
    nodeForFile: (file) ->
        for node in @tbody.children 'tr'
            $node = $(node)
            return $node if file == $node.data('file')

    # add the given file
    addFile: (file) ->
        # show the table if hidden
        @table.addClass 'in' unless @table.hasClass 'in'

        # create a row
        $clone = @sample.clone()
        $clone.find('td:eq(0)').html file.name
        $clone.find('td:eq(1)').html plupload.formatSize(file.size)
        $clone.appendTo @tbody

        # add the file to the row
        $clone.data 'file', file

        # create tooltips
        @createTooltips $clone

    # removes the given file
    removeFile: (file) ->
        # get the node
        $node = @nodeForFile file

        # remove the node
        $node.remove()

        # remove the file from the uploader
        @uploader.removeFile file

        # hide the table if no more elements
        @table.removeClass 'in' unless @tbody.children('tr').length

# initialize input controller on dom ready
$ -> new Input 'body'
