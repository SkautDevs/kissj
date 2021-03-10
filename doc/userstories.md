# Userstories

Stavy účastníka v registraci jsou v pořadí:
 - ověření emailu,
 - vyplnění údajů,
 - poslání ke schválení,
 - schválení a vygenerování platby,
 - potvrzení platby a zaslání finálních informací.

Hlavní flow registrace je naznačený vrchním pásem, který se při průchodu registračním procesem postupně zbarvuje.


## Účastníci

### Registrace IST

IST je člen servis týmu, který se registruje jako jednotlivec. 

 1. zadá svůj mail na landing page a klikne na `Přihlásit se`
 2. do schránky mu přijde mail s tlačítkem `Log in`, tím se dostane zpět do aplikace k výběru role
 3. kliknutím na roli `IST` si tuto roli vybere a dostane se tím na dashboard
 4. klikem na tlačítko `upravit` se dostane k vyplňování osobních údajů
 5. po vyplnění všech údajů klikne na `Uložit`
 6. po vizuální kontrole údajů na dashboardu klikne na `Uzamknout registraci`
 7. v následujícím okně checkboxem potvrdí, že je srozumněn s pravidly akce a ochranou vyplněných údajů a klikne na `Uzamknout registraci`
 8. aplikace tímto přestává být interaktivní, všechny údaje pro účastníka jsou pro úpravy zamčené a primární komunikace už probíhá maily
 9. dostane mail o odemknutí registrace s odůvodněním co změnit, a vrací se k 5. kroku, NEBO
 10. dostane mail o schválení registrace s vygenerovanou platbou
 11. zaplatí podle přijatých údajů
 12. dostane mail s potvrzením o přijetí platby. Zaregistrováno!


### Registrace Patroly

Patrola je skupina účastníků s jedním Patrol Leaderem, který celé skupině zajišťuje registraci včetně platby

1. patrol leader zadá svůj platný mail a klikne na `Přihlásit se`
2. do schránky mu přijde mail s tlačítkem `Log in`
3. klikem na tlačítko se dostane zpět do aplikace k výběru role
4. kliknutím na roli `Patrol Leader` si tuto roli vybere
5. klikem na tlačítko `upravit` se dostane k vyplňování svých osobních údajů
6. po vyplnění všech svých údajů klikne na `Uložit`
7. klikem na tlačítko `přidat účastníka` se dostane k vyplňování osobních údajů dalšího účastníka ve skupině
8. po vyplnění všech údajů dalšího účastníka klikne na `Uložit`
9. krok 7. a 8. opakuje, dokud nejsou zadány údaje pro všechny členy skupiny
10. klikem na ikonku `edit` může ostatní ve skupině upravovat
11. klikem na ikonku `delete` může ostatní ve skupině nenávratně smazat
12. po vizuální kontrole údajů na dashboardu klikne na `Uzamknout registraci`
13. v následujícím okně checkboxem potvrdí, že je srozumněn s providly akce a ochranou jeho údajů a klikne na `Uzamknout registraci`
14. aplikace tímto přestává být interaktivní, všechny údaje pro účastníka jsou pro úpravy zamčené a primární komunikace už probíhá maily
15. dostane mail o odemknutí registrace s odůvodněním co změnit, a vrací se k 5. kroku, NEBO
16. dostane mail o schválení registrace s vygenerovanou platbou pro celou skupinu
17. zaplatí za celou skupinu podle přijatých údajů
18. dostane mail s potvrzením o přijetí platby. Zaregistrováno!


### Registrace hostů

Host je účastníkem na akci, který neplatí. Platby se pro něj úplně přeskakují a řeší se jinou cestou. Na akci se registruje sám.  

1. zadá svůj platný mail a klikne na `Přihlásit se`
2. do schránky mu přijde mail s tlačítkem `Log in`
3. klikem na tlačítko se dostane zpět do plikace k výběru role
4. kliknutím na roli `host` si tuto roli vybere
5. klikem na tlačítko `upravit` se dostane k vyplňování osobních údajů
6. po vyplnění všech údajů klikne na `Uložit`
7. po vizuální kontrole údajů na dashboardu klikne na `Uzamknout registraci`
8. v následujícím okně checkboxem potvrdí, že je srozumněn s providly akce a ochranou jeho údajů a klikne na `Uzamknout registraci`
9. aplikace tímto přestává být interaktivní, všechny údaje pro účastníka jsou pro úpravy zamčené a primární komunikace už probíhá maily
10. dostane mail o odemknutí registrace s odůvodněním co změnit, a vrací se k 5. kroku, NEBO
11. dostane mail o schválení registrace. Zaregistrováno!


## Administrátoři

Administrátoři schvalují účastníky na akci, generují platby a potvrzují je. V případě potřeby jsou schopni měnit údaje i v zamčených registracích. 

Na administrátorském dashboardu vidí rychlý přehled o počtech registrovaných a rozcestník k jednotlivých funkcím v administraci.

Při automatickém zpracovávání se používá fixní počet administrátorských úkonů. Je to proto, aby se nepřehltil systém pro posílání mailů.

## Potvrzení účastníka
1. klikem na `Schvalovat registraci` se otevře seznam všech registrací čekajících na schválení
2. po zkontrolování účastnických informací se klikem na tlačítko `Schválit` odešle mail o schválení s nově vygenerovanou platbu účastníkovi
3. klikem na tlačítko `Neschválit` se otevře okno s možností vložit text, co se v registračních údajích musí opravit
4. po zadání informace o opravě se klikem na tlačítko `Neschválit a odeslat mail` účastníkovi odemkne registrace, kterou může následně opravit a znovu uzamknout

## Potvrzení platby ručně
1. klikem na `Schvalovat platby ručně` se otevře seznam všech plateb čekajících na schválení
2. po zkontrolování platebních informací se klikem na tlačítko `Schválit` odešle mail o přijetí platby a závěrečných informacích. Zaregistrováno!
3. klikem na tlačítko `Zrušit platbu` se otevře okno s možností vložit text, proč se platba ruší (mělo by se využívat minimálně, platba může už být na cestě)
4. po zadání informace o opravě se klikem na tlačítko `Neschválit a odeslat mail` účastníkovi pošle mail s vysvětlením, proč se platba zrušila (účastník se tímto dostane do zamčeného stavu, kdy je potřeba ho nově schválit, nebo odemknout)
5. klikem na tlačítko nahoře `Odemknout platby po splatnosti` se odemkne fixní množství registrací s předdefinovaným důvodem `prošlá lhůta pro zaplacení`

## Potvrzení platby automatcky s bankou
1. klikem na `Spárovat platby automaticky s bankou` se otevře náhled na všechny vygenerované platby a platby z banky
2. klikem na `Stáhnout a napárovat platby` se stáhne aktuální výpis plateb z banky a aplikace napáruje fixní množství plateb z banky s vygenerovanými platbami. 
    V případě shody se účastníkům odešlou maily s potvrzením o zaplacení
3. opětovným klikem na stejné tlačtko se může opakovat krok 2., dokud nejsou vyřízeny všechny platby
4. nenapárované platby stažené z banky se zobrazují v tabulce `Nespárované platby`
5. u neznámé platby klikem na `Vyřízeno` se plata skryje - typicky je poté potřeba spárovat platbu ručně, nebo jde o platbu nesouvisející s registrací

## Export údajů
1. klikem na `Exportovat data` se stáhne tabulka ve formátu `.csv` se všemi potřebnými údaji
