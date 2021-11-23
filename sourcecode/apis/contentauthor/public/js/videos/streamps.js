/** @namespace H5P */
H5P.VideoStreamps = (function ($) {

    function Streamps(sources, options, l10n) {
        var self = this;

        var player;

        var videoId = getId(sources[0].path);
        var id = 'h5p-streamps-' + videoId;

        var $wrapper = $('<div/>');
        var placeholderUrl = [sources[0].path];
        if (!options || options.controls === false) {
            placeholderUrl.push('&hidecontrolbar=1')
        }
        var $placeholder = $('<iframe id="' + id + '" type="text/html" width="640" height="360" src="' + placeholderUrl.join("") + '" frameborder="0"></iframe>').appendTo($wrapper);

        var create = function () {
            if (!$placeholder.is(':visible') || player !== undefined) {
                return;
            }

            if (typeof StreampsPlayer === "undefined") {
                loadAPI(create);
                return;
            }

            var width = $wrapper.width();
            if (width < 200) {
                width = 200;
            }

            player = new StreampsPlayer(id);
            player.events = {
                play: function() {
                    self.trigger('stateChange', H5P.Video.PLAYING);
                },
                pause: function() {
                    self.trigger('stateChange', H5P.Video.PAUSED);
                },
                end: function() {
                    self.trigger('stateChange', H5P.Video.ENDED);
                },
                ready: function() {
                    self.trigger('ready');
                    self.trigger('loaded');
                }
            }
        }

        self.appendTo = function ($container) {
            $container.addClass('h5p-streamps').append($wrapper);
            create();
        };

        /**
                  * Start the video.
                  *
                  * @public
                  */
        self.play = function () {
            if (!player || !player.controls.play) {
                self.on('ready', self.play);
                return;
            }
            player.controls.play();
        };


        /**
                  * Pause the video.
                  *
                  * @public
                  */
        self.pause = function () {
            self.off('ready', self.play);
            if (!player || !player.controls.pause) {
                return;
            }
            player.controls.pause();
        };

        /**
                  * Seek video to given time.
                  *
                  * @public
                  * @param {Number} time
                  */
        self.seek = function (time) {
            if (!player || !player.controls.seek) {
                return;
            }
            player.controls.seek(parseInt(time));
        };

        /**
         * Get elapsed time since video beginning.
         *
         * @public
         * @returns {Number}
         */
        self.getCurrentTime = function () {
            if (!player || !player.getPlayerOffsetFloat) {
                return;
            }
            return player.getPlayerOffsetFloat();
        };

        self.pressToPlay = navigator.userAgent.match(/iPad/i) ? true : false;

        /**
         * Get total video duration time.
         *
         * @public
         * @returns {Number}
         */
        self.getDuration = function () {
            if (!player || !player.getVideoDuration) {
                return;
            }

            return player.getVideoDuration() || 0;
        };


        /**
         * Get percentage of video that is buffered.
         *
         * @public
         * @returns {Number} Between 0 and 100
         */
        self.getBuffered = function () {
            if (!player || !player.getBufferedOffset) {
                return;
            }
            var buffered = player.getBufferedOffset() / self.getDuration() * 100;
            return buffered <= 100 ? buffered : 100;
        };

        /**
         * Turn off video sound.
         *
         * @public
         */
        self.mute = function () {
            if (!player || !player.controls.mute) {
                return;
            }

            player.controls.mute();
        };

        /**
         * Turn on video sound.
         *
         * @public
         */
        self.unMute = function () {
            if (!player || !player.controls.unmute) {
                return;
            }

            player.controls.unmute();
        };

        /**
         * Check if video sound is turned on or off.
         *
         * @public
         * @returns {Boolean}
         */
        self.isMuted = function () {
            if (!player || !player.isMuted) {
                return;
            }

            return player.isMuted();
        };

        /**
         * Return the video sound level.
         *
         * @public
         * @returns {Number} Between 0 and 100.
         */
        self.getVolume = function () {
            if (!player || !player.getVolume) {
                return;
            }

            return player.getVolume();
        };

        /**
         * Set video sound level.
         *
         * @public
         * @param {Number} level Between 0 and 100.
         */
        self.setVolume = function (level) {
            if (!player || !player.controls.volume) {
                return;
            }

            player.controls.volume(level);
        };

        // Respond to resize events by setting the YT player size.
        self.on('resize', function () {
            if (!$wrapper.is(':visible')) {
                return;
            }

            if (!player) {
                // Player isn't created yet. Try again.
                create();
                return;
            }

            // Use as much space as possible
            $wrapper.css({
                width: '100%',
                height: '100%'
            });

            var width = $wrapper[0].clientWidth;
            var height = options.fit ? $wrapper[0].clientHeight : (width * (9 / 16));

            // Set size
            $wrapper.css({
                width: width + 'px',
                height: height + 'px'
            });

            $placeholder.attr({
                width: width,
                height: height
            });
        });
    }

    Streamps.canPlay = function (sources) {
        return sources[0].mime === 'video/Streamps' && getId(sources[0].path) !== null;
    }

    /**
     * Load the IFrame Player API asynchronously.
     */
    var loadAPI = function (loaded) {
        if (typeof SDKLoaded !== "undefined") {
            // Someone else is loading, hook in
            var original = SDKLoaded;
            SDKLoaded = function () {
                loaded();
                original();
            };
        }
        else {
            // Load the API our self
            var tag = document.createElement('script');
            tag.src = "https://videoapi.streamps.net/playerSdk/v1/sdk.js";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            Streamps.loaderInterval = setInterval(function () {
                if (typeof StreampsPlayer !== "undefined") {
                    clearInterval(Streamps.loaderInterval);
                    SDKLoaded(Streamps.id);
                }
            }, 50);
            SDKLoaded = loaded;
        }
    };

    var SDKLoaded;

    var getId = function (url) {
        var matches = url.match(/(?:[^\/]+\/video\/[^\/]+\/)([A-Za-z0-9_-]+)\?appid=[^&]+&signexpiry=[^&]+&signature=[^&]+/i);
        if (matches && matches[1]) {
            return matches[1];
        }
    }

    return Streamps;
})(H5P.jQuery);

// Register video handler
H5P.videoHandlers = H5P.videoHandlers || [];
H5P.videoHandlers.push(H5P.VideoStreamps);
