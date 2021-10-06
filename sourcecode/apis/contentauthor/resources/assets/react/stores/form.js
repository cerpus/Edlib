
const form = (state, action) => {
    switch (action.type) {
        case actions.setPublish: {
            const {
                published,
            } = action.payload;

            return {
                ...state,
                isPublished: published,
            };
        }
        case actions.setTitle: {
            const { title } = action.payload;
            return {
                ...state,
                title,
            };
        }
        case actions.setLicense: {
            const { license } = action.payload;
            return {
                ...state,
                license,
            };
        }
        case actions.setSharing: {
            const { isPrivate } = action.payload;
            return {
                ...state,
                share: isPrivate !== true ? 'share' : 'private',
            };
        }
        case actions.setDisplayOptions: {
            const {
                frame,
                download,
                copyright,
            } = action.payload;
            return {
                ...state,
                frame,
                download,
                copyright,
            };
        }
        case actions.setLanguage: {
            const {
                language,
                isNewVariant,
            } = action.payload;
            return {
                ...state,
                isNewLanguageVariant: isNewVariant,
                language_iso_639_3: language,
            };
        }
        case actions.setError: {
            const {
                messages,
                messageTitle,
            } = action.payload;
            return {
                ...state,
                messages,
                messageTitle,
            };
        }
        case actions.resetError:
            return {
                ...state,
                messages: [],
                messageTitle: '',
            };
        case actions.setContent:
            return {
                ...state,
                content: action.payload.content,
            };
        case actions.setQuestionSetData: {
            const {
                content: {
                    title,
                },
                content,
            } = action.payload;

            return {
                ...state,
                title,
                questionSetJsonData: content,
            };
        }
        case actions.setEmbed:
            return {
                ...state,
                link: action.payload.link,
            };
    }
};

const actions = {
    setPublish: 'SET_PUBLISH',
    setTitle: 'SET_TITLE',
    setLicense: 'SET_LICENSE',
    setSharing: 'SET_SHARING',
    setDisplayOptions: 'SET_DISPLAY_OPTIONS',
    setLanguage: 'SET_LANGUAGE',
    setError: 'SET_ERROR',
    resetError: 'RESET_ERROR',
    setContent: 'SET_CONTENT',
    setQuestionSetData: 'SET_QUESTIONSET_DATA',
    setEmbed: 'SET_EMBED',
};


export {
    form as default,
    actions,
};
