export const startTimer = () => Date.now();
export const stopTimer = (startTime, name) =>
    console.log(`Used ${Date.now() - startTime}ms for ${name}`);
