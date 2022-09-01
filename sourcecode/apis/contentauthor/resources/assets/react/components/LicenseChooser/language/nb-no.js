'use strict';

let byHelp = 'Lisenselementet «Navngivelse» kan ikke velges bort, og forekommer alltid. Dette innebærer at for all bruk er det påbudt at opphavspersonen navngis slik som god skikk tilsier. Dette er forøvrig i samsvar med norsk lov.';
let saHelp = 'Lisenselementet «Del på samme vilkår» innebærer et påbud om at bearbeidelser av verket kun kan spres på samme vilkår som det opprinnelige verket. Dette prinsippet (at avledede verk skal være underlagt samme vilkår som det eller de verk de er avledet fra) kalles ofte for «Copyleft»';
let ndHelp = 'Lisenselementet «Ingen bearbeidelse» tillater kun spredning og bruk av verket i uendret tilstand. Bearbeidelse eller endring av verket er forbudt. Det samme gjelder framstilling av såkalte avledede verk. Bruker du en lisens med dette elementet på ditt eget verk avskjærer du andre fra å lage remixer og mashups der ditt verk inngår.';
let ncHelp = 'Lisenselementet «Ikkekommersiell» tillater kun spredning og bruk under den forutsetning at verket ikke benyttes på en kommersiell måte. Er dette lisens­elementet valgt er bruk til reklameformål, ervervsmessig bruk, og annen bruk som har til formål å gi kommersiell nytte, eller som gir økonomisk gevinst, forbudt. Ønsker du å sikre deg mot at ditt verk brukes til reklame eller at kommersielle aktører benytter verket uten å gjøre en eksplisitt avtale med deg først, må du velge en av de tre lisensene som inneholder dette lisenselementet.';

export default {
    'LICENSECHOOSER.YES': 'Ja',
    'LICENSECHOOSER.NO': 'Nei',

    'LICENSECHOOSER.ADAPTIONS': 'TILLAT AT BEARBEIDELSER AV DITT VERK BLIR DELT?',
    'LICENSECHOOSER.OPTION-SHAREALIKE': 'Ja, så lenge andre deler på samme vilkår',

    'LICENSECHOOSER.COMMERCIAL-USE': 'TILLAT KOMMERSIELL BRUK AV VERKET DITT?',

    'LICENSECHOOSER.ATTRIBUTION-TITLE': 'RETTIGHETSHAVER DETALJER',
    'LICENSECHOOSER.ATTRIBUTION-FIELD-TITLE': 'Tittel',
    'LICENSECHOOSER.ATTRIBUTION-FIELD-NAME': 'Rettighetshaver',
    'LICENSECHOOSER.ATTRIBUTION-FIELD-URL': 'URL',
    'LICENSECHOOSER.ATTRIBUTION-FIELD-TITLE-PLACEHOLDER': 'Tittel på verket',
    'LICENSECHOOSER.ATTRIBUTION-FIELD-NAME-PLACEHOLDER': 'Ditt navn',
    'LICENSECHOOSER.ATTRIBUTION-FIELD-URL-PLACEHOLDER': 'Skriv inn URL',

    'LICENSECHOOSER.RESTRICTION-LEVEL': 'GRAD AV FORBEHOLD',
    'LICENSECHOOSER.PUBLIC-DOMAIN': 'Ingen rettigheter forbeholdt',
    'LICENSECHOOSER.CREATIVE-COMMONS': 'Noen rettigheter forbeholdt',
    'LICENSECHOOSER.COPYRIGHT': 'Alle rettigheter forbeholdt',

    'LICENSECHOOSER.ATTRIBUTION-HELP': 'Dette muliggjør brukere av dette verket å finne ut hvordan de kan kontakte deg for mer informasjon angående verket.',

    'LICENSECHOOSER.ADAPTIONS-HELP': '<p><strong>Ja</strong>{nl}' +
        'Lisensgiver tillater andre å kopiere, distribuere, vise, skrive ut og fremføre verket/frembringelsen, samt å lage og distribuere bearbeidelser basert på dette.</p>' +
        '<p><strong>Ja, så lenge andre deler på samme vilkår.</strong>{nl}' + saHelp + '</p>' +
        '<p><strong>Nei</strong>{nl}' + ndHelp + '</p>',

    'LICENSECHOOSER.COMMERCIAL-USE-HELP': '<p><strong>Ja</strong>{nl}' +
        'Lisensgiver tillater andre å kopiere, distribuere, vise, skrive ut og fremføre verket/frembringelsen, inklusive for kommersielle formål.</p>' +
        '<p><strong>Nei</strong>{nl}' + ncHelp + '</p>',

    'LICENSECHOOSER.RESTRICTION-LEVEL-HELP': '<p><strong>Ingen rettigheter forbeholdt</strong>{nl}Velg denne lisensen hvis du er rettighetsholder og vil fraside deg alle rettigheter, hvis noen, til innholdet på verdensomspennende nivå. Dette kan være tilfellet hvis du reproduserer innhold allerede i Public Domain og vil konnunisere at du ikke krever kopiretten selv om loven gir deg denne.</p>' +
        '<p><strong>Noen rettigheter forbeholdt</strong>{nl}Velg denne lisensen hvis du vil bruke en Creative Commons lisens.{nl}' + byHelp + '</p>' +
        '<p><strong>Edlib lisens</strong>{nl}Du bestemmer over innholdet. Men innholdet kan brukes av Edlib til bl.a. markedsføring.</p>',

    'LICENSECHOOSER.PUBLICDOMAIN': 'Velg en Public Domain lisens',
    'LICENSECHOOSER.PUBLICDOMAIN.HELP': '<p><strong>Creative Commons Zero</strong>{nl}' +
        'Dette verktøyet kan benyttes utenfor Norge dersom du er rettighetshaver til et verk, og ønsker å dedikere verket til det fri utenfor Norge.</p>' +
        '<p><strong>Public Domain Mark</strong>{nl}' +
        'Bruk dette verktøyet for å identifisere et verk som har falt i det fri.</p>',

    'LICENSECHOOSER.PUBLICDOMAIN.CC0': 'Creative Commons Zero',
    'LICENSECHOOSER.PUBLICDOMAIN.PDM': 'Public Domain Mark',
    'LICENSECHOOSER.EDLL': 'Edlib lisens',
};
