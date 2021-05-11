import styled from 'styled-components';
import React from 'react';

import Accordion from './iconFiles/Accordion.svg';
import AppearIn from './iconFiles/AppearIn.svg';
import Audio from './iconFiles/Audio.svg';
import Blanks from './iconFiles/Blanks.svg';
import CategoryTask from './iconFiles/CategoryTask.svg';
import Chart from './iconFiles/Chart.svg';
import Collage from './iconFiles/Collage.svg';
import Column from './iconFiles/Column.svg';
import CoursePresentation from './iconFiles/CoursePresentation.svg';
import DialogCards from './iconFiles/DialogCards.svg';
import Discussion from './iconFiles/Discussion.svg';
import DocumentationTool from './iconFiles/DocumentationTool.svg';
import DragQuestion from './iconFiles/DragQuestion.svg';
import DragText from './iconFiles/DragText.svg';
import FindMultipleHotspots from './iconFiles/FindMultipleHotspots.svg';
import FindTheHotspot from './iconFiles/FindTheHotspot.svg';
import FlashCards from './iconFiles/FlashCards.svg';
import GuessTheAnswer from './iconFiles/GuessTheAnswer.svg';
import IFrameEmbed from './iconFiles/IFrameEmbed.svg';
import ImageHotspots from './iconFiles/ImageHotspots.svg';
import ImagePairing from './iconFiles/ImagePairing.svg';
import ImageSequencing from './iconFiles/ImageSequencing.svg';
import InteractiveVideo from './iconFiles/InteractiveVideo.svg';
import MarkTheWords from './iconFiles/MarkTheWords.svg';
import MemoryGame from './iconFiles/MemoryGame.svg';
import MultiChoice from './iconFiles/MultiChoice.svg';
import OrderPriority from './iconFiles/OrderPriority.svg';
import Questionnaire from './iconFiles/Questionnaire.svg';
import QuestionSet from './iconFiles/QuestionSet.svg';
import SequenceProcess from './iconFiles/SequenceProcess.svg';
import SingleChoiceSet from './iconFiles/SingleChoiceSet.svg';
import Summary from './iconFiles/Summary.svg';
import SummaryKeywords from './iconFiles/SummaryKeywords.svg';
import ThreeImage from './iconFiles/ThreeImage.svg';
import Timeline from './iconFiles/Timeline.svg';
import TrueFalse from './iconFiles/TrueFalse.svg';
import TwitterUserFeed from './iconFiles/TwitterUserFeed.svg';

const iconFiles = {
    Accordion,
    AppearIn,
    Audio,
    Blanks,
    CategoryTask,
    Chart,
    Collage,
    Column,
    CoursePresentation,
    DialogueCards: DialogCards,
    Dialogcards: DialogCards,
    Discussion,
    DocumentationTool,
    DragQuestion,
    DragText,
    FindMultipleHotspots,
    FindTheHotspot,
    ImageHotspotQuestion: FindTheHotspot,
    Flashcards: FlashCards,
    GuessTheAnswer,
    IFrameEmbed,
    ImageHotspots,
    ImagePairing,
    ImageSequencing,
    InteractiveVideo,
    MarkTheWords,
    MemoryGame,
    MultiChoice,
    OrderPriority,
    Questionnaire,
    QuestionSet,
    SequenceProcess,
    SingleChoiceSet,
    Summary,
    SummaryKeywords,
    ThreeImage,
    Timeline,
    TrueFalse,
    TwitterUserFeed,
    CerpusVideo: InteractiveVideo,
};

const StyledH5PIcon = styled.div`
    display: inline-flex;
    width: ${(props) => props.theme.rem(props.sizeRem)};
    height: ${(props) => props.theme.rem(props.sizeRem)};

    & > img {
        width: ${(props) => props.theme.rem(props.sizeRem)};
        height: ${(props) => props.theme.rem(props.sizeRem)};
    }
`;

const getIcon = (name) => {
    if (!name) {
        return null;
    }

    const icon = Object.entries(iconFiles).find(
        ([key, src]) => key.toLowerCase() === name.toLowerCase()
    );

    if (!icon) {
        return null;
    }

    return icon[1];
};

const H5PIcon = ({ name, fontSizeRem = 1 }) => {
    const src = getIcon(name);

    if (!src) {
        console.warn('H5P icon with name ' + name + ' was not found');
    }

    return (
        <StyledH5PIcon sizeRem={fontSizeRem}>
            {src ? <img src={src} alt="" /> : ''}
        </StyledH5PIcon>
    );
};

export default H5PIcon;
