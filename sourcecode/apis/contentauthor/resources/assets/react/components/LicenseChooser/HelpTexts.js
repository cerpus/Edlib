import { FormattedMessage } from 'react-intl';
import { makeStyles } from '@material-ui/core/styles';

const useStyle = makeStyles((theme) => ({
    option: {
        marginTop: '1.5em',
        marginBottom: '1.5em',
    },
    heading: {
        fontSize: '1.2em',
        fontWeight: 'bold',
        marginBottom: '0.25em',
    },
    content: {
        marginLeft: '1em',
    },
}));

const PublicDomainText = () => {
    const classes = useStyle();

    return (
        <div>
            <div className={classes.option}>
                <div className={classes.heading} >
                    <FormattedMessage id="LICENSECHOOSER.PUBLICDOMAIN.CC0"/>
                </div>
                <div className={classes.content}>
                    <FormattedMessage id="LICENSECHOOSER.PUBLICCOMAIN.CC0-TEXT"/>
                </div>
            </div>
            <div className={classes.option}>
                <div className={classes.heading} >
                    <FormattedMessage id="LICENSECHOOSER.PUBLICDOMAIN.PDM"/>
                </div>
                <div className={classes.content}>
                    <FormattedMessage id="LICENSECHOOSER.PUBLICCOMAIN.PDM-TEXT"/>
                </div>
            </div>
        </div>
    );
};

const AdaptionsText = () => {
    const classes = useStyle();

    return (
        <div>
            <div className={classes.option}>
                <div className={classes.heading} >
                    <FormattedMessage id="LICENSECHOOSER.YES"/>
                </div>
                <div className={classes.content}>
                    <FormattedMessage id="LICENSECHOOSER.ADAPTIONS-HELP.YES-TEXT"/>
                </div>
            </div>
            <div className={classes.option}>
                <div className={classes.heading} >
                    <FormattedMessage id="LICENSECHOOSER.NO"/>
                </div>
                <div className={classes.content}>
                    <FormattedMessage id="LICENSECHOOSER.ADAPTIONS-HELP.NO-TEXT"/>
                </div>
            </div>
            <div className={classes.option}>
                <div className={classes.heading} >
                    <FormattedMessage id="LICENSECHOOSER.ADAPTIONS-HELP.SA"/>
                </div>
                <div className={classes.content}>
                    <FormattedMessage id="LICENSECHOOSER.ADAPTIONS-HELP.SA-TEXT"/>
                </div>
            </div>
        </div>
    );
};

const CommercialUseText = () => {
    const classes = useStyle();

    return (
        <div>
            <div className={classes.option}>
                <div className={classes.heading} >
                    <FormattedMessage id="LICENSECHOOSER.YES"/>
                </div>
                <div className={classes.content}>
                    <FormattedMessage id="LICENSECHOOSER.COMMERCIAL-USE.YES-TEXT"/>
                </div>
            </div>
            <div className={classes.option}>
                <div className={classes.heading} >
                    <FormattedMessage id="LICENSECHOOSER.NO"/>
                </div>
                <div className={classes.content}>
                    <FormattedMessage id="LICENSECHOOSER.COMMERCIAL-USE.NO-TEXT"/>
                </div>
            </div>
        </div>
    );
};

const RestrictionLevelText = () => {
    const classes = useStyle();

    return (
        <div>
            <div className={classes.option}>
                <div className={classes.heading} >
                    <FormattedMessage id="LICENSECHOOSER.PUBLIC-DOMAIN"/>
                </div>
                <div className={classes.content}>
                    <FormattedMessage id="LICENSECHOOSER.RESTRICTION-LEVEL.PD-TEXT"/>
                </div>
            </div>
            <div className={classes.option}>
                <div className={classes.heading} >
                    <FormattedMessage id="LICENSECHOOSER.CREATIVE-COMMONS"/>
                </div>
                <div className={classes.content}>
                    <FormattedMessage id="LICENSECHOOSER.RESTRICTION-LEVEL.CC-TEXT-1"/>
                    <br/>
                    <FormattedMessage id="LICENSECHOOSER.RESTRICTION-LEVEL.CC-TEXT-2"/>
                </div>
            </div>
            <div className={classes.option}>
                <div className={classes.heading} >
                    <FormattedMessage id="LICENSECHOOSER.EDLL"/>
                </div>
                <div className={classes.content}>
                    <FormattedMessage id="LICENSECHOOSER.RESTRICTION-LEVEL.EDLL-TEXT"/>
                </div>
            </div>
        </div>
    );
};

export {
    PublicDomainText,
    AdaptionsText,
    CommercialUseText,
    RestrictionLevelText,
};
