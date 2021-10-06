(function ($, H5PEditor, EventDispatcher) {

    let scriptsLoaded = false;

    class CustomImageEditingPopup extends EventDispatcher {

        ratio = null;
        cropper = null;
        imageSrc;
        offset;
        maxWidth;
        maxHeight;

        constructor(ratio) {
            super();

            this.ratio = ratio;

            const background = document.createElement('div');
            background.className = 'h5p-editing-image-popup-background hidden';

            const popup = document.createElement('div');
            popup.className = 'h5p-editing-image-popup';
            background.appendChild(popup);

            const header = document.createElement('div');
            header.className = 'h5p-editing-image-header';
            popup.appendChild(header);

            const headerTitle = document.createElement('div');
            headerTitle.className = 'h5p-editing-image-header-title';
            headerTitle.textContent = H5PEditor.t('core', 'editImage');
            header.appendChild(headerTitle);

            const headerButtons = document.createElement('div');
            headerButtons.className = 'h5p-editing-image-header-buttons';
            header.appendChild(headerButtons);

            const editingContainer = document.createElement('div');
            editingContainer.className = 'h5p-cropper-editing-image-editing-container';
            popup.appendChild(editingContainer);

            this.addActionbar(editingContainer);

            const editingBox = document.createElement('div');
            editingBox.className = 'h5p-editing-image-editingbox';
            editingContainer.appendChild(editingBox);

            const imageLoading = document.createElement('div');
            imageLoading.className = 'h5p-editing-image-loading';
            imageLoading.textContent = H5PEditor.t('core', 'loadingImageEditor');
            popup.appendChild(imageLoading);

            // Create editing image
            const editingImage = new Image();
            editingImage.className = 'h5p-editing-image hidden';
            editingImage.style.display = 'block';
            editingImage.style.maxWidth = '100%';
            editingImage.id = 'h5p-editing-image-' + Math.floor(Math.random() * 1000);
            editingImage.addEventListener('ready', () => {
                H5P.$body.get(0).classList.add('h5p-editor-image-popup');
                background.classList.remove('hidden');
                imageLoading.classList.add('hidden');
                this.trigger('initialized');
            });
            editingBox.appendChild(editingImage);

            this.show = (offset, imageSrc) => {
                this.imageSrc = { ...imageSrc };
                this.offset = { ...offset };
                H5P.$body.get(0).appendChild(background);
                background.classList.remove('hidden');
                if( imageSrc ){
                    H5P.setSource(editingImage, imageSrc, H5PEditor.contentId);
                    if (!scriptsLoaded) {
                        this.loadScript('/js/cropperjs/cropper.min.js', data => {
                            this.imageLoaded(offset);
                            this.cropper = this.initCropper(editingImage);
                            scriptsLoaded = true;
                        });
                    } else {
                        if( !this.cropper){
                            this.cropper = this.initCropper(editingImage);
                            this.imageLoaded(offset);
                        } else {
                            this.imageLoaded(offset);
                            this.cropper.replace(imageSrc.path);
                        }
                    }
                } else {
                    H5P.$body.get(0).classList.add('h5p-editor-image-popup');
                    background.classList.remove('hidden');
                    this.trigger('initialized');
                }
            };

            this.imageLoaded = offset => {
                if (offset) {
                    const imageLoaded = () => {
                        this.adjustPopupOffset(offset);
                        editingImage.removeEventListener('load', imageLoaded);
                    };

                    editingImage.addEventListener('load', imageLoaded);
                }
            }

            this.hide = () => {
                H5P.$body.get(0).classList.remove('h5p-editor-image-popup');
                background.classList.add('hidden');
                H5P.$body.get(0).removeChild(background);
                editingContainer.dispatchEvent(new Event('reset'));

                this.trigger('canceled');
            };

            this.setImage = imgSrc => {
                this.imageLoaded(this.offset);
                this.cropper.replace(imgSrc.path);
            };

            this.adjustPopupOffset = function (offset) {
                if (offset) {
                    this.topOffset = offset.top;
                }

                const dims = CustomImageEditingPopup.staticDimensions;

                // Only use 65% of screen height
                const maxScreenHeight = screen.height * dims.maxScreenHeightPercentage;

                // Calculate editor max height
                const backgroundHeight = H5P.$body.get(0).offsetHeight - dims.backgroundPaddingHeight;
                const popupHeightNoImage = dims.editorToolbarHeight + dims.popupHeaderHeight +
                    dims.editorPadding;
                const editorHeight =  backgroundHeight - popupHeightNoImage;

                // Available editor height
                const availableHeight = maxScreenHeight < editorHeight ? maxScreenHeight : editorHeight;

                // Check if image is smaller than available height
                let actualImageHeight;
                if (editingImage.naturalHeight < availableHeight) {
                    actualImageHeight = editingImage.naturalHeight;
                }
                else {
                    actualImageHeight = availableHeight;

                    // We must check ratio as well
                    const maxWidth = background.offsetWidth - dims.backgroundPaddingWidth -
                        dims.editorPadding;
                    const imageRatio = editingImage.naturalHeight / editingImage.naturalWidth;
                    const maxActualImageHeight = maxWidth * imageRatio;
                    if (maxActualImageHeight < actualImageHeight) {
                        actualImageHeight = maxActualImageHeight;
                    }
                }

                const popupHeightWImage = actualImageHeight + popupHeightNoImage;
                let offsetCentered = this.topOffset - (popupHeightWImage / 2) -
                    (dims.backgroundPaddingHeight / 2);

                // Min offset is 0
                offsetCentered = offsetCentered > 0 ? offsetCentered : 0;

                // Check that popup does not overflow editor
                if (popupHeightWImage + offsetCentered > backgroundHeight) {
                    const newOffset = backgroundHeight - popupHeightWImage;
                    offsetCentered = newOffset < 0 ? 0 : newOffset;
                }

                popup.style.top = offsetCentered + 'px';
                editingBox.style.height =  actualImageHeight + 'px';
            };

            /**
             * Create header button
             *
             * @param {string} coreString Must be specified in core translations
             * @param {string} className Unique button identifier that will be added to classname
             * @param {function} clickEvent OnClick function
             */
            const createButton = (coreString, className, clickEvent) => {
                const button = document.createElement('button');
                button.textContent = H5PEditor.t('core', coreString);
                button.className = className;
                button.addEventListener('click', clickEvent);
                headerButtons.appendChild(button);
            }

            createButton('resetToOriginalLabel', 'h5p-editing-image-reset-button h5p-remove', () => {
                editingContainer.dispatchEvent(new Event('reset'));
                this.trigger('resetImage');
                this.imageSrc = { ...this.imageSrc.originalImage };
            });

            createButton('cancelLabel', 'h5p-editing-image-cancel-button', () => {
                this.trigger('canceled');
                this.hide();
            });

            createButton('saveLabel', 'h5p-editing-image-save-button h5p-done', () => {
                H5P.setSource(editingImage, this.imageSrc, H5PEditor.contentId);
                this.trigger('imageSaved', this.imageSrc);
                this.hide();
            });
        }

        readyToCrop(){
            const croppedData = this.cropper.getData(true);
            if (!(croppedData.width && croppedData.height)){
                return;
            }
            const imageData = this.cropper.getImageData();

            const startX = croppedData.x * 100 / imageData.naturalWidth;
            const startY = croppedData.y * 100 / imageData.naturalHeight;
            const endX = (croppedData.x + croppedData.width) * 100 / imageData.naturalWidth;
            const endY = (croppedData.y + croppedData.height) * 100 / imageData.naturalHeight;

            let imageSrc = this.imageSrc;
            $.ajax({
                url: H5PIntegration.editor.ajaxPath + 'imageManipulation&imageId=' + this.imageSrc.externalId,
                data: {
                    startY,
                    startX,
                    endY,
                    endX
                },
                success: url => {
                    if( !imageSrc.originalImage){
                        imageSrc.originalImage = { ...imageSrc };
                    }
                    imageSrc.path = url;
                    imageSrc.width = croppedData.width;
                    imageSrc.height = croppedData.height;
                    this.setImage(imageSrc);
                },
                async: true
            });
        }

        addActionbar(parentElement){

            const action = () => {
                if( cropActions.classList.toggle('h5p-editing-image-active-actions') ){
                    this.cropper.setDragMode('crop');
                    cropButton.classList.add('h5p-editing-action-active-button');
                } else {
                    this.cropper.clear();
                    this.cropper.setDragMode('none');
                    cropButton.classList.remove('h5p-editing-action-active-button');
                }
            }

            const actionbar = document.createElement('div');
            actionbar.className = 'h5p-editing-image-actionbar';
            parentElement.appendChild(actionbar);
            parentElement.addEventListener('reset', event => {
                cropActions.classList.remove('h5p-editing-image-active-actions');
                this.cropper.setDragMode('none');
            })

            const cropButton = document.createElement('button');
            cropButton.type = 'button';
            cropButton.className = 'material-icons h5p-editing-action-button';
            cropButton.innerText = 'crop';
            cropButton.onclick = action;
            actionbar.appendChild(cropButton);

            const doneButton = document.createElement('button');
            doneButton.type = 'button';
            doneButton.className = 'material-icons h5p-editing-action-crop-done';
            doneButton.innerText = 'done';
            doneButton.onclick = () => {
                this.readyToCrop();
                action();
            }

            const cancelButton = document.createElement('button');
            cancelButton.type = 'button';
            cancelButton.className = 'material-icons h5p-editing-action-crop-cancel';
            cancelButton.innerText = 'close';
            cancelButton.onclick = action;

            const cropActions = document.createElement('div');
            cropActions.className = 'h5p-editing-image-actions';
            cropActions.appendChild(doneButton);
            cropActions.appendChild(cancelButton);
            actionbar.appendChild(cropActions);
        }

        initCropper(editingImage){
            return new Cropper(editingImage, {
                viewMode: 1,
                background: false,
                rotatable: false,
                scalable: false,
                zoomable: false,
                autoCrop: false,
                dragMode: 'none',
                ready: () => {
                    this.resizeModal();
                }
            });
        }

        resizeModal() {
            this.cropper.dragBox.style.height = this.cropper.canvas.style.height
            this.cropper.dragBox.style.width = this.cropper.canvas.style.width
            this.cropper.dragBox.style.transform = this.cropper.canvas.style.transform
        }

        /**
         * Load a script dynamically
         *
         * @param {string} path Path to script
         * @param {function} [callback]
         */
        loadScript(path, callback) {
            $.ajax({
                url: path,
                dataType: 'script',
                success: function (data) {
                    if (callback) {
                        callback(data);
                    }
                },
                async: true
            });
        };

    }

    CustomImageEditingPopup.staticDimensions = {
        backgroundPaddingWidth: 32,
        backgroundPaddingHeight: 96,
        editorPadding: 64,
        editorToolbarHeight: 40,
        maxScreenHeightPercentage: 0.65,
        popupHeaderHeight: 59
    };

    CustomImageEditingPopup.prototype = Object.create(CustomImageEditingPopup.prototype);
    CustomImageEditingPopup.prototype.constructor = CustomImageEditingPopup;

    const originalImageEditorPopup = H5PEditor.ImageEditingPopup;
    H5PEditor.ImageEditingPopup = CustomImageEditingPopup;
})(H5P.jQuery, H5PEditor, H5P.EventDispatcher);
