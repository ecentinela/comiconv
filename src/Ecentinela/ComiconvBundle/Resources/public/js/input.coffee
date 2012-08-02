# input controller
Input = can.Control
    # drag the tr
    '.icon-move draginit': ($drag, e, drag) ->
        # set the node on the dragged element
        $drag.data('node', $drag.closest 'tr')

        # create the drag
        drag.ghost().html "<span class='label label-info' style='margin-left:15px'></span>"

    # dragged element over a droppable
    'tbody tr dropover': ($drop, e, drop, drag) ->
        # add class to icons
        $drop.find('i').addClass 'icon-white'

        # get the dragged node
        $drag = drag.element.data 'node'

        # span on the dragged ghost
        $span = drag.movingElement.find 'span'

        # if dropping over same node
        if $drag.is $drop
            $drop.addClass 'dropover invalid'

            $span.html "Can't be moved here"
        # dropped over other node
        else
            $drop.addClass 'dropover'

            dragFilename = $drag.data('file').name
            dropFilename = $drop.data('file').name

            $span.html "Insert #{dragFilename} before #{dropFilename}"

    # dragged element out a droppable
    'tbody tr dropout': ($drop) ->
        # remove class to icons
        $drop.find('i').removeClass 'icon-white'

        # remove classes on drop element
        $drop.removeClass 'dropover invalid'

    # dropped the element
    'tbody tr dropon': ($drop, e, drop, drag) ->
        # remove class to icons
        $drop.find('i').removeClass 'icon-white'

        # remove classes on drop element
        $drop.removeClass 'dropover invalid'

        # get the dragged tr
        $drag = drag.element.data 'node'

        # if dropping on same elemtn
        if $drag.is $drop
            # revert the drag
            drag.revert()
        else
            # insert the node before the dropped element
            $drag.insertBefore $drop

    # click on the trash icon to remove a file
    '.icon-trash click': ($el, e) ->
        # hide the tooltip manually
        $el.tooltip 'hide'

        # remove the file
        @removeFile $el.closest('tr').data('file')

    # upload the files
    '.btn-primary click': ($el) ->
        # disable button
        $el.prop 'disabled', 'disabled'

        # show fader
        @fader.addClass 'in'

        # reorder uploader files
        @uploader.files = ($(node).data 'file' for node in @tbody.find 'tr')
        for file, i in @uploader.files
            file.params = total: @uploader.files.length, num: i + 1, hash: HASH

        # initialize upload
        @uploader.start()

    # click on the retry button
    '.icon-repeat click': ($el, e) ->
        # get the file
        file = $el.closest('tr').data 'file'

        # set the file on the uploader
        @uploader.files = [file]

        # initialize upload
        @uploader.start()

    # constructor
    init: ->
        # get elements
        @container = @element.find '.container'
        @uploaderError = @element.find '#requirement-error.hide'
        @drop = @element.find '#drop'
        @fader = @element.find '#fader'
        @progress = @element.find '#progress'
        @progressDiv = @progress.find 'div'
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
                @element.addClass 'dragenter'
            .on 'dragexit', =>
                @element.removeClass 'dragenter'
            .on 'drop', =>
                @element.removeClass 'dragenter'

            # prepare upload
            @uploader = new plupload.Uploader
                runtimes: 'html5,flash'
                browse_button : 'upload-link'
                drop_element: 'body'
                url: Routing.generate('upload', _locale: ExposeTranslation.locale)
                flash_swf_url: '/bundles/ecentinelacomiconv/Resources/public/plupload/js/plupload.flash.swf'
                filters: [
                    title: 'Comic files (jpg, pdf, cbz)'
                    extensions: 'jpg,pdf,cbz'
                ]
                init:
                    FilesAdded: (up, files) =>
                        @addFile file for file in files

                    BeforeUpload: (up, file) =>
                        # set file params
                        up.settings.multipart_params = file.params

                        # get the file tr node
                        $node = @nodeForFile file

                        # set the progress bar position
                        @progress.addClass('in').css $node.offset()

                        # add the node class
                        $node.addClass 'uploading'

                    UploadProgress: (up, file) =>
                        # update progress bar
                        percent = file.loaded * @progress.width() / file.size
                        @progressDiv.width "#{percent}%"

                    FileUploaded: (up, file, response) =>
                        # hide the progress
                        @progress.removeClass 'in'

                        # get the file tr node
                        $node = @nodeForFile file

                        # toggle node classes
                        $node.removeClass('uploading').addClass('uploaded');

                    UploadComplete: (up) =>
                        # hide the progress
                        @progress.removeClass 'in'

                        # redirect if no failed files
                        location.href = Routing.generate('output', _locale: ExposeTranslation.locale, hash: HASH) unless up.total.failed

                    Error: (up, response) =>
                        # get the file
                        file = response.file

                        # get the node with the file
                        $node = @nodeForFile file

                        # mark the node as error
                        $node.addClass 'error'

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
