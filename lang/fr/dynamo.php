<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang strings for the dynamo module.
 *
 * @package    mod_dynamo
 * @copyright  2019 UCLouvain
 * @author     Dominique Palumbo 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['modulename']                         = 'Dynamo';
$string['modulenameplural']                   = 'Dynamo';
$string['modulename_help']                    = 'Dynamo est un outil d\'évaluation par les pairs. Cette version n\'est pas stable et n\'est pas finie.';
$string['dynamoname']                         = 'Nom';
$string['dynamoname_help']                    = 'Aide toi et Moodle t\'aidera';
$string['mod_dynamo']                         = 'Dynamo';
$string['pluginadministration']               = 'Dynamo administration';
$string['pluginname']                         = 'Dynamo';

$string['dynamofieldset']                     = 'Personnalisation des critères';
$string['dynamoauto']                         = 'Exiger que chaque étudiant s\'auto évalue';
$string['dynamogroupeval']                    = 'Exiger que chaque étudiant évalue globalement son groupe';
$string['dynamoautotitle']                    = 'Autoévaluation';
$string['dynamogroupevaltitle']               = '&nbsp;';
$string['dynamochoice']                       = 'Groupement ciblé';
$string['dynamocritparticipation']            = 'Informations complémentaires (facultatif)';
$string['dynamocritresponsabilite']           = 'Informations complémentaires (facultatif)';
$string['dynamocritscientifique']             = 'Informations complémentaires (facultatif)';
$string['dynamocrittechnique']                = 'Informations complémentaires (facultatif)';
$string['dynamocritattitude']                 = 'Informations complémentaires (facultatif)';
$string['dynamocritparticipationdefault']     = 'rencontres, ponctualité, respect des règles du groupe';
$string['dynamocritresponsabilitedefault']    = 'accomplissement des tâches confiées par le groupe : (lectures, recherches, rédaction, etc)';
$string['dynamocritscientifiquedefault']      = 'pertinence et qualité des recherches et des idées, compréhension des concepts';
$string['dynamocrittechniquedefault']         = 'méthodologie de travail, utilisation des outils pour la réalisation des activités, créativité';
$string['dynamocritattitudedefault']          = 'attitude positive favorisant l’avancement du travail, un bon climat et de bonnes relations dans l’équipe';
$string['dynamocrit1']                        = 'Critère 1';
$string['dynamocrit2']                        = 'Critère 2';
$string['dynamocrit3']                        = 'Critère 3';
$string['dynamocrit4']                        = 'Critère 4';
$string['dynamocrit5']                        = 'Critère 5';
$string['dynamocrit6']                        = 'Critère 6';
$string['dynamocritoptname']                  = 'Critère 6 optionnel (facultatif)';
$string['dynamocritoptnamedescr']             = 'Description du critère 6';
$string['dynamostudenttitle']                 = 'Evaluation de la dynamique de groupe de l\'activité';
$string['dynamoteacherlvl1title']             = 'Evaluation de la dynamique de groupe';
$string['dynamoteacherlvl1evalother']         = 'Evalue ses pairs';
$string['dynamoteacherlvl1othereval']         = 'est évalué(e) PAR ses pairs';
$string['dynamolegend']                       = 'Légende';
$string['dynamogrid']                         = 'Grille des critères';
$string['dynamoparticipation']                = 'Participation';
$string['dynamoresponsabilite']               = 'Responsabilité';
$string['dynamoscientifique']                 = 'Expertise Scientifique';
$string['dynamotechnique']                    = 'Expertise Technique';
$string['dynamoattitude']                     = 'Attitude Générale';
$string['dynamoeval1']                        = 'Très insatisfaisante';
$string['dynamoeval2']                        = 'Insatisfaisante';
$string['dynamoeval3']                        = 'Honnête, ni +, ni -';
$string['dynamoeval4']                        = 'Très bonne';
$string['dynamoeval5']                        = 'Excellente';
$string['dynamocommentcontr']                 = 'Commentaires sur ma contribution dans le groupe';
$string['dynamocommentfonction']              = 'Commentaires sur le fonctionnement du groupe en général';
$string['dynamonotfilled']                    = 'Tous les champs ne sont pas remplis. Merci de les remplir avant de soumettre l\'évaluation';
$string['dynamosavedsuccessfully']            = 'Votre évaluation a été sauvée avec succès. Merci. Vous allez être redirigé rapidement vers votre cours.';
$string['dynamosavedcorrupted']               = 'Les données envoyées ne sont pas correctes ou absentes. Merci de réessayer, svp. (back sur votre navigateur)';
$string['dynamoevalgroup']                    = 'Evaluation par le groupe';
$string['dynamoevalofgroup']                  = 'Evaluation du groupe';
$string['dynamosum']                          = '&sum;';
$string['dynamoavg']                          = 'Moyenne';
$string['dynamostddev']                       = 'Ecart Type';
$string['dynamogroup']                        = 'Groupe';
$string['dynamogroupevalby']                  = 'Autoévaluation moyenne du groupe';
$string['dynamogroupevaluatedby']             = 'Moyenne des évaluations des pairs';
$string['dynamoheadgroup']                    = 'Groupe';
$string['dynamoheadgrouping']                 = 'Groupement';
$string['dynamoheaddate']                     = 'Date';
$string['dynamoheadfirstname']                = 'Prénom';
$string['dynamoheadlastname']                 = 'Nom';
$string['dynamoheadevalfirstname']            = 'Evaluateur prénom';
$string['dynamoheadevallastname']             = 'Evaluateur nom';
$string['dynamoheademail']                    = 'Email';
$string['dynamoheadidnumber']                 = 'NOMA';
$string['dynamogrpingreport']                 = 'Rapport Groupement';
$string['dynamoier']                          = 'Total';
$string['dynamoniwf']                         = 'Taux d\'implication relatif';
$string['dynamoevaluator']                    = 'Evaluateur';
$string['dynamoevaluated']                    = 'Evalué';
$string['dynamoradar01title']                 = 'Radar';
$string['dynamoradar01title2']                = 'Auto évaluation vs moyenne des évaluations par les pairs';
$string['dynamoradar01title3']                = 'Auto évaluation vs moyenne des évaluations par les pairs vs Auto évaluation du groupe';
$string['dynamoradar01title4']                = 'Toutes les autoévaluations';
$string['dynamoavgeval']                      = 'Moyenne des évaluations';
$string['dynamoautoeval']                     = 'Autoévaluation';
$string['dynamoopenclose']                    = 'Ouvrir/fermer';
$string['dynamogotogroup']                    = 'Détail du groupe';
$string['dynamogotoparticipant']              = 'Détail des participants';
$string['dynamogroupdetailtitle']             = 'Rapport du groupe';
$string['dynamoconf']                         = 'Assurance';
$string['dynamopreview']                      = 'Prévisualisation';
$string['dynamotab1']                         = 'Prévisualisation';
$string['dynamotab2']                         = 'Résultats';
$string['dynamotab3']                         = 'Rapports';
$string['dynamoresults1']                     = 'Vue d\'ensemble';
$string['dynamoresults2']                     = 'Détail du groupe';
$string['dynamoresults3']                     = 'Détail du participant';
$string['dynamoliststudent']                  = 'Liste des étudiants';
$string['dynamotypeletters']                  = 'Tapez les premières lettres du nom ou du prénom';
$string['dynamoheadparticiaption']            = 'Répondu';
$string['dynamoheadimplication']              = 'Implication';
$string['dynamoheadconfidence']               = 'Assurance';
$string['dynamoheadcohesion']                 = 'Cohésion';
$string['dynamoheadconflit']                  = 'Appréciations';
$string['dynamoheadremarque']                 = 'Climat';
$string['dynamonocomment']                    = 'Sans commentaire spécifique';
$string['dynamoreport01']                     = 'Liste des participants sans réponse';
$string['dynamoreport02']                     = 'Rapports des groupes';
$string['dynamoreport03']                     = 'Rapports individuels';
$string['dynamoreport04']                     = 'Graphique des assurances relatives';
$string['dynamoreport05']                     = 'Trombinoscope';
$string['dynamoreport06']                     = 'Export Excel';
$string['dynamosendmail']                     = 'Envoye de mails';
$string['dynamononoparticipant']              = 'Tous les étudiants ont rempli le formulaire';
$string['dynamoreports']                      = 'Liste des Rapports';
$string['dynamogotogroup']                    = 'Allez au groupe';
$string['dynamoreportselect']                 = 'Choisissez votre Rapport';
$string['dynamoremovegroupnoprobs']           = 'Masquer les groupes sans problémes';
$string['dynamoremovegroupnotcomplete']       = 'Masquer les groupes incomplets';
$string['dynamoswitchoverview']               = 'Changer de vue';
$string['dynamorepbtsynthesis']               = 'Masquer Rapport';
$string['dynamorepbtniwf']                    = 'Masquer NIWF';
$string['dynamorepbtevalothers']              = 'Masquer évaluation des pairs';
$string['dynamorepbtevalbyothers']            = 'Masquer évaluation par les pairs';
$string['dynamorepbtgraphradar']              = 'Masquer radars';
$string['dynamorepbtgraphhisto']              = 'Masquer histogrammes';
$string['dynamorepbtcomment']                 = 'Masquer commentaires';
$string['dynamoremovecolors']                 = 'Supprimer les couleurs des chiffres';
$string['dynamogroupcount']                   = 'Nombre de groupes';
$string['dynamostudentcount']                 = 'Nombre d\'étudiants';
$string['dynamostudentnoanswerscount']        = 'Nombre d\'étudiants sans réponse';
$string['dynamokeywords']                     = 'plagi|absent|présent|dommage|expertise|problème|dispense|retard|conflit';
$string['dynamogroupetypefan']                = 'L\'école des fans';
$string['dynamogroupetyperas']                = 'Groupe homogène';
$string['dynamogroupetypeclustering']         = 'Clustering';
$string['dynamogroupetypeclique']             = 'Clique';
$string['dynamogroupetypeheterogene']         = 'Hetérogène';
$string['dynamogroupetypeghost']              = 'Fantômes';
$string['dynamopleasewait']                   = 'Merci de votre patience...';
$string['dynamohelp']                         = '<div class="box-niwf">
                                                    <math xmlns="http://www.w3.org/1998/Math/MathML"><mi>N</mi><mi>I</mi><mi>W</mi><msub><mi>F</mi><mrow><mo>&#xA0;</mo><mi>j</mi><mo>&#x2260;</mo><mi>i</mi><mo>&#xA0;</mo></mrow></msub><mo>=</mo><munderover><mo>&#x2211;</mo><mrow><mi>i</mi><mo>=</mo><mn>1</mn></mrow><mi>n</mi></munderover><mo>(</mo><mfrac><mrow><msub><mi>R</mi><mrow><mi>j</mi><mo>&#x2260;</mo><mi>i</mi></mrow></msub><mo>&#xA0;</mo></mrow><mrow><munderover><mo>&#x2211;</mo><mrow><mi>k</mi><mo>=</mo><mn>1</mn></mrow><mi>n</mi></munderover><mo>&#xA0;</mo><msub><mi>R</mi><mrow><mi>K</mi><mo>&#x2260;</mo><mi>i</mi></mrow></msub></mrow></mfrac><mo>)</mo></math>
                                                </div>';
$string['dynamonotyetdesigned']               = 'Cette fonctionnalité n\'est pas encore implémentée';
$string['dynamotop']                          = 'Haut';

$string['dynamogotodetail']                   = 'Défiler jusqu\'à';
$string['dynamohelpniwf']                     = 'NIWF: facteur de pondération individuelle normalisé (à l\'exclusion de l\'auto-évaluation)';
$string['dynamomenuresults']                  = 'Résultats';
$string['dynamomenureports']                  = 'Rapports';

$string['privacy:metadata:dynamo_eval']           = 'Informations sur l\'évaluation de l\'utilisateur sur une activité dynamo donnée';
$string['privacy:metadata:dynamo_eval:evalbyid']  = 'L\'utilisateur qui fait l\'évaluation.';
$string['privacy:metadata:dynamo_eval:userid']    = 'L\'utilisateur qui est évalué.';

$string['dynamoexcelready']                   = 'Cliquez sur l\'icône pour télécharger votre document';
$string['dynamographauto']                    = 'Auto';
$string['dynamographpeers']                   = 'Pairs';

$string['dynamoactivityview']                 = 'Les étudiants doivent afficher et sauver cette activité pour la terminer';