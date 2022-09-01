'use strict';

let byHelp = '<p>Lisenselementet «Navngivelse» kan ikke velges bort, og forekommer alltid. Dette innebærer at for all bruk er det påbudt at opphavspersonen navngis slik som god skikk tilsier. Dette er forøvrig i samsvar med norsk lov.</p>';
let saHelp = '<p>Lisenselementet «Del på samme vilkår» innebærer et påbud om at bearbeidelser av verket kun kan spres på samme vilkår som det opprinnelige verket. Dette prinsippet (at avledede verk skal være underlagt samme vilkår som det eller de verk de er avledet fra) kalles ofte for «Copyleft»</p>';
let ndHelp = '<p>Lisenselementet «Ingen bearbeidelse» tillater kun spredning og bruk av verket i uendret tilstand. Bearbeidelse eller endring av verket er forbudt. Det samme gjelder framstilling av såkalte avledede verk. Bruker du en lisens med dette elementet på ditt eget verk avskjærer du andre fra å lage remixer og mashups der ditt verk inngår.</p>';
let ncHelp = '<p>Lisenselementet «Ikkekommersiell» tillater kun spredning og bruk under den forutsetning at verket ikke benyttes på en kommersiell måte. Er dette lisens­elementet valgt er bruk til reklameformål, ervervsmessig bruk, og annen bruk som har til formål å gi kommersiell nytte, eller som gir økonomisk gevinst, forbudt. Ønsker du å sikre deg mot at ditt verk brukes til reklame eller at kommersielle aktører benytter verket uten å gjøre en eksplisitt avtale med deg først, må du velge en av de tre lisensene som inneholder dette lisenselementet.</p>';
let noLicenseHelp = '<p>Ingen lisens er angitt.</p>';

export default {
    'LICENSE.PRIVATE': 'Opphavsrett',
    'LICENSE.PRIVATE.HELP': 'Alle rettigheter forbeholdt',
    'LICENSE.COPYRIGHT': 'Opphavsrett',
    'LICENSE.COPYRIGHT.HELP': 'Alle rettigheter forbeholdt',
    'LICENSE.CC0': 'Creative Commons Zero',
    'LICENSE.CC0.HELP': 'Dette verktøyet kan benyttes utenfor Norge dersom du er rettighetshaver til et verk, og ønsker å dedikere verket til det fri utenfor Norge.',
    'LICENSE.BY': 'CC Navngivelse',
    'LICENSE.BY.HELP': byHelp,
    'LICENSE.BY-SA': 'CC Navngivelse-Del på samme vilkår',
    'LICENSE.BY-SA.HELP': byHelp + saHelp,
    'LICENSE.BY-ND': 'CC Navngivelse-Ingen bearbeidelse',
    'LICENSE.BY-ND.HELP': byHelp + ndHelp,
    'LICENSE.BY-NC': 'CC Navngivelse-Ikkekommersiell',
    'LICENSE.BY-NC.HELP': byHelp + ncHelp,
    'LICENSE.BY-NC-SA': 'Navngivelse-Ikkekommersiell-Del på samme vilkår',
    'LICENSE.BY-NC-SA.HELP': byHelp + ncHelp + saHelp,
    'LICENSE.BY-NC-ND': 'Navngivelse-Ikkekommersiell-Ingen bearbeidelse',
    'LICENSE.BY-NC-ND.HELP': byHelp + ncHelp + ndHelp,
    'LICENSE..HELP': noLicenseHelp,
    'LICENSE.PDM': 'Public Domain Mark',
    'LICENSE.PDM.HELP': 'Bruk dette verktøyet for å identifisere et verk som har falt i det fri.',
    'LICENSE.EDLL': 'Edlib lisens',
    'LICENSE.EDLL.HELP': 'Du gir EdLib (selskapet) en verdensomspennende, ikke-eksklusiv, royalty-fri, overførbar lisens (med rett til underlisensiering) for å bruke, reprodusere, distribuere, utarbeide avledede verk av, vise og utføre det innsendte innholdet i forbindelse med levering av tjenesten og ellers i forbindelse med levering av tjenesten og selskapets virksomhet, inkludert uten begrensning for markedsføring og omfordeling av deler av eller hele tjenesten (og avledede verk derav) i medieformater og gjennom mediekanaler.',
};
