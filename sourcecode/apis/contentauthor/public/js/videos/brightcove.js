/** @namespace H5P */
H5P.VideoBrightcove = (function ($) {

    function Brightcove(sources, options, l10n) {
        var self = this;

        var player;

        var videoId = getId(sources[0].path);

        const accountId = '4806596774001';
        const playerId = is360Video(sources[0].path) ? 'oB5bDFv99' : 'BkLm8fT';
        var $wrapper = $('<div/>');

        var $placeholder = $('<div id="brightcove-player-container"></div>').appendTo($wrapper);

        this.textTrackMapping = [];
        this.playerTracks = [];
        self.loaded = false;

        const filterTracks = player => {
            const tracks = [];
            const playerTextTracks = player.textTracks();
            for(let i = 0; i < playerTextTracks.length; i++){
                const track = playerTextTracks[i];
                if( typeof track.default !== 'undefined'){
                    tracks.push(playerTextTracks[i]);
                }
            }
            return tracks;
        }

        var create = function () {
            if (!$placeholder.is(':visible') || player !== undefined || self.loaded === true) {
                return;
            }

            if (typeof brightcovePlayerLoader === "undefined") {
                loadAPI(create);
                return;
            }

            self.loaded = true;
            brightcovePlayerLoader({
                refNode: $placeholder.get(0),
                accountId: accountId,
                playerId: playerId,
                videoId: videoId,
                onSuccess: function (success) {
                    player = success.ref;
                    player.controls(options.controls === true);
                    player.loop(options.loop === true);
                    player.autoplay(options.autoplay === true);
                    player.fluid = options.fit;
                    if( options.controls !== true){
                        player.bigPlayButton.hide();
                    }
                    player.on('loadedmetadata', function () {
                        self.trigger('loaded');
                        self.playerTracks = filterTracks(player);
                        if (self.playerTracks.length > 0){
                            setTimeout(() => {
                                self.trigger('captions', self.playerTracks.map((track, index) => {
                                    self.textTrackMapping.push(track.id);
                                    return new H5P.Video.LabelValue(track.label,index);
                                }));
                            }, 0);
                        }
                    });
                    player.on('play', function() {self.trigger('stateChange', H5P.Video.PLAYING)});
                    player.on('pause', function() {self.trigger('stateChange', H5P.Video.PAUSED)});
                    player.on('ended', function() {self.trigger('stateChange', H5P.Video.ENDED)});
                    player.on('ready', function() {
                        self.trigger('ready');
                    });
                },
                onFailure: function (error) {
                    console.log(error);
                }
            })
        };

        self.appendTo = function ($container) {
            $container.addClass('h5p-brightcove').append($wrapper);
            create();
        };

        /**
         * Start the video.
         *
         * @public
         */
        self.play = function () {
            if (!player || !player.play) {
                self.on('ready', self.play);
                return;
            }
            player.play();
        };


        /**
         * Pause the video.
         *
         * @public
         */
        self.pause = function () {
            self.off('ready', self.play);
            if (!player || !player.pause) {
                return;
            }
            player.pause();
        };

        /**
         * Seek video to given time.
         *
         * @public
         * @param {Number} time
         */
        self.seek = function (time) {
            if (!player || !player.currentTime) {
                return;
            }
            player.currentTime(parseInt(time));
        };

        /**
         * Get elapsed time since video beginning.
         *
         * @public
         * @returns {Number}
         */
        self.getCurrentTime = function () {
            if (!player || !player.currentTime) {
                return;
            }
            return player.currentTime();
        };

        self.pressToPlay = !!navigator.userAgent.match(/iPad/i);

        /**
         * Get total video duration time.
         *
         * @public
         * @returns {Number}
         */
        self.getDuration = function () {
            if (!player) {
                return;
            }

            return player.duration() || 0;
        };


        /**
         * Get percentage of video that is buffered.
         *
         * @public
         * @returns {Number} Between 0 and 100
         */
        self.getBuffered = function () {
            if (!player || !player.bufferedPercent) {
                return;
            }
            return player.bufferedPercent() * 100;
        };

        /**
         * Turn off video sound.
         *
         * @public
         */
        self.mute = function () {
            if (!player || !player.muted) {
                return;
            }

            player.muted(true);
        };

        /**
         * Turn on video sound.
         *
         * @public
         */
        self.unMute = function () {
            if (!player || !player.muted) {
                return;
            }

            player.muted(false);
        };

        /**
         * Check if video sound is turned on or off.
         *
         * @public
         * @returns {Boolean}
         */
        self.isMuted = function () {
            if (!player || !player.muted) {
                return;
            }

            player.muted();
        };

        /**
         * Return the video sound level.
         *
         * @public
         * @returns {Number} Between 0 and 100.
         */
        self.getVolume = function () {
            if (!player || !player.volume) {
                return;
            }

            return player.volume() * 100;
        };

        /**
         * Set current captions track.
         *
         * @param {H5P.Video.LabelValue} Captions track to show during playback
         */
        self.setCaptionsTrack = function (currentTrack) {
            const mappedTrack = currentTrack ? this.textTrackMapping[currentTrack.value] : null;
            this.playerTracks.forEach(track => {
                track.mode = mappedTrack === track.id ? 'showing' : 'disabled';
            })
        };

        /**
         * Figure out which captions track is currently used.
         *
         * @return {H5P.Video.LabelValue} Captions track
         */
        self.getCaptionsTrack = function () {
            const tracks = this.playerTracks.filter(track => track.mode === 'showing')
                .map((track, index) => new H5P.Video.LabelValue(track.label, index));

            return tracks.length > 0 ? tracks[0] : null;
        };

        /**
         * Set video sound level.
         *
         * @public
         * @param {Number} level Between 0 and 100.
         */
        self.setVolume = function (level) {
            if (!player || !player.volume) {
                return;
            }
            let volume = level / 100;
            if (volume < 0) {
                volume = 0;
            }
            if (volume > 100) {
                volume = 100;
            }
            player.volume(volume);
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

            player.dimensions(width, height);
        });
    }

    Brightcove.canPlay = function (sources) {
        return sources[0].mime === 'video/Brightcove' && getId(sources[0].path);
    };

    var loadAPI = function (loaded) {
        if (typeof SDKLoaded !== "undefined") {
            // Someone else is loading, hook in
            var original = SDKLoaded;
            SDKLoaded = function () {
                loaded();
                original();
            };
        } else {
            // Load the API our self
            var tag = document.createElement('script');
            tag.src = "/build/js/videos/brightcove-player-loader.min.js";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            Brightcove.loaderInterval = setInterval(function () {
                if (typeof brightcovePlayerLoader !== "undefined") {
                    clearInterval(Brightcove.loaderInterval);
                    SDKLoaded();
                }
            }, 50);
            SDKLoaded = loaded;
        }
    };

    var SDKLoaded;

    var getId = function (url) {
        var matches = url.match(/^https:\/\/bc\/?(0|360)?\/(ref:[a-z0-9]+|\d+)$/);
        if (matches && matches.length > 1) {
            return matches[matches.length - 1];
        }
    };

    // https://bc/12345678 => No path, will use 360 player
    // https://bc/0/12345678 => Path is 0, will use normal player
    // https://bc/360/12345678 => Path is 360, will use 360 player
    var is360Video = function (url) {
        return /^https:\/\/bc\/(360|[1-9]\d*)/.test(url);
    };

    return Brightcove;
})(H5P.jQuery);

// Register video handler
H5P.videoHandlers = H5P.videoHandlers || [];
H5P.videoHandlers.push(H5P.VideoBrightcove);
