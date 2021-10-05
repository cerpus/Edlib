$('#content-form').submit(function (event) {
    event.preventDefault();
    if (typeof storeContent !== "function") {
        alert("Missing vital function 'storeContent'");
        return;
    }

    function submit(){
        const $errorBox = $("#content-form-error-box");
        $errorBox.hide();
        storeContent(form.getAttribute('action'), form, function (response) {
            let responseData;
            try {
                responseData = JSON.parse(response.responseText);
            } catch (err) {
                responseData = [response.responseText];
            }
            $errorBox.show();
            const $errorList = $errorBox.find('ul');
            $errorList.html("");
            $.each(responseData.errors, function (key, value) {
                $errorList.append($("<li>").html(value));
            });
        });
    }

    function isFormDataReady(){
        let jsonParams = JSON.parse(form.elements.questionSetJsonData.value);
        const questionsNotReady = jsonParams.cards.filter(card => !card.question.readyForSubmit);
        const answersNotReady = jsonParams.cards.filter(card => card.answers.filter(answer => !answer.readyForSubmit).length > 0);

        return questionsNotReady.length === 0 && answersNotReady.length === 0;
    }

    const form = this;
    let attempts = 0;
    const loaderInterval = setInterval(function(){
        if( isFormDataReady() === true || attempts >= 20){
            clearInterval(loaderInterval);
            submit();
        }
        attempts++;
    }, 20);
});

function submitEditorForm(){
    $('#content-form').submit();
}
