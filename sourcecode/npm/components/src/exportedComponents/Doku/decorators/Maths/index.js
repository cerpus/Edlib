import InlineTex from './InlineTex';
import atomicTypes from '../../../../config/atomicTypes';

export default {
    strategy: (contentBlock, callback, contentState) => {
        contentBlock.findEntityRanges((character) => {
            const entityKey = character.getEntity();

            return (
                entityKey !== null &&
                contentState.getEntity(entityKey).getType() === atomicTypes.MATH
            );
        }, callback);
    },
    component: InlineTex,
};
