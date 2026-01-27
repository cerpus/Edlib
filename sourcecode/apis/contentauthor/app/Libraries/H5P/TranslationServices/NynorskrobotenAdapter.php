<?php

namespace App\Libraries\H5P\TranslationServices;

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use GuzzleHttp\Client;
use SensitiveParameter;

use const JSON_THROW_ON_ERROR;

final readonly class NynorskrobotenAdapter implements TranslationServiceInterface
{
    public function __construct(
        private Client $client,
        #[SensitiveParameter]
        private string $apiToken,
    ) {}

    public function translate(string $toLanguage, H5PTranslationDataObject $data): H5PTranslationDataObject
    {
        $response = $this->client->post('translate', [
            'json' => $this->convertSourceToObject($data),
        ]);
        $responseData = json_decode(
            $response->getBody()->getContents(),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        return new H5PTranslationDataObject($responseData['document'], $responseData['guid']);
    }

    private function convertSourceToObject(H5PTranslationDataObject $data): array
    {
        return [
            'token' => $this->apiToken,
            'guid' => $data->getId() ?? '',
            'fileType' => 'htmlp',
            'document' => $data->getFields(),
            'prefs' => $this->getPrefs(),
        ];
    }

    public function getSupportedLanguages(): array|null
    {
        return [
            'nob' => ['nno'],
        ];
    }

    private function getPrefs(): array
    {
        return [
            "language" => "nob-nno",
            "tenkje-leggje.kons-kj2k_gj2g" => true,
            "infa_infe" => true,
            "me_vi" => true,
            "vart-vorte_blei-blitt.vb-bli2verte" => true,
            "blæs_blåser.vb" => true,
            "symje_svømme.stav" => true,
            "augne_auge.stav" => true,
            "stove_stue.vok-u2o" => true,
            "voks_vaks.vok-o2a" => true,
            "samd_einig.syn" => true,
            "førebels_foreløpig.syn" => true,
            "etterspurnad_etterspørsel.syn" => true,
            "tryggleik_sikkerheit.syn" => true,
            "mengd_mengde.vok-2e" => true,
            "banen_bana.n-m2f" => [
                "bygning",
                "demning",
                "festning", "frysning",
                "kledning", "krusning",
                "ladning", "ledning",
                "munning",
                "spenning", "strekning",
            ],
            "håpa_håpte.vb-e2a" => [
                "ape",
                "beite", "brøyte",
                "deise", "dreise",
                "feile", "fike", "fire", "fleipe", "fløyte",
                "gape", "geipe", "glime", "gruse",
                "huse", "hyre", "håpe",
                "ise",
                "kruse", "kule",
                "lade", "lave", "leike", "leite", "lóse", "love", "lute",
                "meie",
                "peike", "peise", "prise",
                "rane", "rape", "rose", "ruse", "ruve",
                "smeise", "snuse", "spleise", "sprute", "sprøyte", "stane", "stile", "strene", "streve", "sture", "sveise", "sveive", "sveve", "syre",
                "trene", "tøve", "tøyser",
                "veiver", "våge",
            ],
            "medan_mens.syn" => true,
            "enkje_enke.kons-kj2k_gj2g" => true,
            "fremje_fremme.kons-mj2mm" => true,
            "elvar_elver.n-pl-e2a" => true,
            "vit_vett" => true,
            "lét_let" => true,
            "nærare_nærmare.stav" => true,
            "fjøre_fjære.vok-ø2æ" => true,
            "gle_glede.vb-inf" => true,
            "lide_li.vb-inf" => true,
            "gløymsle_gløymsel.kons-sel2sle" => true,
            "brud_brur.stav" => true,
            "apos_fot.teikn,oppmode_oppfordre.syn" => true,
            "itilfelle_ifall" => true,
            "byrje_begynne.syn" => true,
            "lagnad_skjebne.syn" => true,
            "fornøgd_nøgd.syn" => true,
            "også_og.syn" => true,
            "røyndom_verkelegheit.syn" => true,
            "i-røynda_i-verkelegheita.syn" => true,
            "nærast_nærmast.stav" => true,
            "medviten_bevisst.syn" => true,
            "avskil_avskjed.syn" => true,
            "overta_ta-over.syn" => true,
            "sitat.lastå" => true,
        ];
    }
}
