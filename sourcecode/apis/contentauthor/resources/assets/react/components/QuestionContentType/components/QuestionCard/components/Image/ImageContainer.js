import React, { Component } from 'react';
import PropTypes from 'prop-types';
import ImageLayout from './ImageLayout';
import Axios from 'utils/axiosSetup';
import { Image } from '../../../utils';

export default class ImageContainer extends Component {
    static propTypes = {
        imageStoreUrl: PropTypes.string,
        onChange: PropTypes.func,
        readOnly: PropTypes.bool,
        image: PropTypes.oneOfType([PropTypes.instanceOf(Image)]),
    };

    static defaultProps = {
        imageStoreUrl: '/questionsets/image',
        readOnly: false,
        image: null,
    };

    constructor(props) {
        super(props);

        this.state = {
            addImageDialogOpen: false,
            imageId: props.image !== null ? props.image.id : null,
            previewImage: props.image !== null ? props.image.url : null,
            uploadProgress: 0,
            enlargeImage: false,
            anchorElement: null,
            uploading: false,
        };

        this.handleDropImage = this.handleDropImage.bind(this);
        this.handleOnChange = this.handleOnChange.bind(this);
        this.handleDataLoaded = this.handleDataLoaded.bind(this);
        this.handleImageClick = this.handleImageClick.bind(this);
        this.handleRemoveImage = this.handleRemoveImage.bind(this);
    }

    handleOnChange() {
        const imageObject = new Image();
        imageObject.id = this.state.imageId;
        imageObject.url = this.state.previewImage;

        this.props.onChange(imageObject, 'image');
    }

    handleDataLoaded({ data }) {
        this.setState({
            imageId: data.file,
            uploading: false,
        }, this.handleOnChange);
    }

    handleDropImage(files) {
        files.forEach(file => {
            this.setState({
                previewImage: file.preview,
                uploading: true,
            });

            const data = new FormData();
            data.append('file', file);
            const config = {
                onUploadProgress: progressEvent => this.setState({
                    uploadProgress: Math.round((progressEvent.loaded * 100) / progressEvent.total),
                }),
            };

            Axios.post(this.props.imageStoreUrl, data, config).then(this.handleDataLoaded);
        });
    }

    handleImageClick(event) {
        this.setState({
            enlargeImage: !this.state.enlargeImage,
            anchorElement: event.currentTarget,
        });
    }

    handleRemoveImage() {
        window.URL.revokeObjectURL(this.state.previewImage);
        this.setState({
            imageId: null,
            previewImage: null,
            enlargeImage: false,
        }, this.handleOnChange);
    }

    render() {
        return (
            <ImageLayout
                onDrop={this.handleDropImage}
                previewImage={this.state.previewImage}
                enlargeImage={this.state.enlargeImage}
                onClick={this.handleImageClick}
                anchorElement={this.state.anchorElement}
                onRemoveImage={this.handleRemoveImage}
                uploadProgress={this.state.uploadProgress}
                readOnly={this.props.readOnly}
                uploading={this.state.uploading}
            />
        );
    }
}
