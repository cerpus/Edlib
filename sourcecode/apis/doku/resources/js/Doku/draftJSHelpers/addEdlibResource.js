import addAtomicBlock from './addAtomicBlock';
import atomicTypes from '../config/atomicTypes';

export default async (useResource, editorState, edlibId) => {
    const { launch: launchUrl } = await useResource(edlibId);

    return addAtomicBlock(editorState, atomicTypes.EDLIB_RESOURCE, {
        edlibId,
        launchUrl,
    });
};
