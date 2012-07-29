# input controller
Input = can.Control
    # constructor
    init: ->
        # get elements
        @container = @element.find '.container'
        @uploaderError = @element.find '#requirement-error.hide'

        # prepare uploader
        @prepareUploader()

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
            uploader = new plupload.Uploader
                runtimes: 'html5,flash'
                browse_button : 'upload-link'
                drop_element: 'drop-area'
                multi_selection: false
                url: Routing.generate 'input'
                flash_swf_url: '/bundles/ecentinelacomiconv/Resources/public/plupload/js/plupload.flash.swf'
                filters: [
                    title: 'Comic files (jpg, pdf, cbz)'
                    extensions: 'jpg,pdf,cbz'
                ]
                init:
                    FilesAdded: =>
                        console.log 'added'

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

            uploader.init()

# initialize input controller on dom ready
$ -> new Input 'body'
