Leírás a megoldáshoz:
(a leírást, a verzió kezelő linken lévő projekt is tartalmazza a "HAZIFELADAT" - mappában.)

A feladat megoldásához a Laravel keretrendszert választottam, de kerültem a keretrendszer specifikus eszközök használatát.
API - szinten valósítottam meg. "\api" route-ra POST requestel JSON formátumban kell az adatokat beküldeni.

Az adatokat (DB) -t a \HAZIFELADAT mappába raktam, de migrálni is lehet: "php artisan migrate:fresh --seed".



A feladatot kettő féle képpen is megoldottam.:

1. "modul/componens" szerű módon. ( \App\Services\EPK_SF\EPK_SingleFile.php )

2. Osztályokra kidolgozva, jól átlátható, értelmezhető, karbantartható módon ( \App\Services\EPK\... )

A két külön álló, de azonos feladatot ugyan azzal a tesztel tesztelhetjük:
(Ha megoldást szeretnénk váltani, akkor a \routes\api.php -ban tudjuk változtatni, hogy melyik változattal dolgozzon a teszt vagy a tesztelő.)

\tests\Feature\...
	exampleDataTest.php 	- A megkapott "exampleData" -kal tesztelem.
	patternTest.php		- A beküldött entitás mintájának helyességét és helytelenségére adott reakciót tesztelem.
	valueTest.php		- A beküldött entitás értékeinek helyességét és helytelenségére adott reakciót tesztelem.
	ruleTest.php		- A feladatban megfogalmazott szabályok helyességét és helytelenségére adott reakciót tesztelem. 


Esetleges hibák és anomáliák:
(Amiket nem tudtam, vagy nem lehetett értelmezni a leírásból)

1. Ha a jelentkező informatikát választ és matematika a kötelező (ELTE), de egy nem kötelező, se nem elvárt tárgyból (olasz nyelv) ér el 20% alatti pontot, akkor a jelentkezés érvényes marad.

2. Minden esetben a magasabb pontszámú tárgy kerül figyelembe vételre akkor is ha csak 1% -kal jobb, ilyenkor ugye a +50 (emelt) nem kerül kiértékelésre.

3. Az output hiba mintája eltér. (egységesebb a validációs hibákhoz mérten)

5. Mind a két megoldás tartalmaz hardkódolt paraméterek a belépési pontjaiknál. (Ezt élesben, egy komplexebb rendszerben perzisztáljuk valahonnan)

4. HTTP(POST) Json kommunikációban valósítottam meg.