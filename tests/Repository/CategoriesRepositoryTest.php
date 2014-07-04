<?php
/**
 * Created by PhpStorm.
 * User: jjuszkiewicz
 * Date: 21.05.2014
 * Time: 12:43
 */

namespace Nokaut\ApiKit\Repository;


use CommerceGuys\Guzzle\Plugin\Oauth2\Oauth2Plugin;
use Nokaut\ApiKit\Collection\Categories;
use Nokaut\ApiKit\Entity\Category;
use Nokaut\ApiKit\Entity\Category\Path;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class CategoriesRepositoryTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var CategoriesRepository
     */
    private $sut;
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $clientApiMock;

    public function setUp()
    {
        $oauth2 = new Oauth2Plugin();
        $accessToken = array(
            'access_token' => '1111'
        );
        $oauth2->setAccessToken($accessToken);
        $this->clientApiMock = $this->getMock('Nokaut\ApiKit\ClientApi\ClientApiInterface', array('send'));

        $this->sut = new CategoriesRepository("http://32213:454/api/v2/", $this->clientApiMock);
    }

    public function testFetchByParentId()
    {
        $this->clientApiMock->expects($this->once())->method('send')
            ->will($this->returnValue($this->getCategoriesFromApiClient()));

        /** @var Categories $categories */
        $categories = $this->sut->fetchByParentId(686);

        $this->assertCount(1, $categories);
        /** @var Category $category */
        $category = $categories->getItem(0);
        $this->assertEquals(687, $category->getId());
        $this->assertEquals(0.24, $category->getCpcValue());
        $this->assertEquals(2, $category->getDepth());
        $this->assertEquals(null, $category->getIsAdult());
        $this->assertEquals(true, $category->getIsVisible());
        $this->assertEquals("Telefony komórkowe", $category->getTitle());
        $this->assertEquals(686, $category->getParentId());
        $this->assertEquals("07b3eb9f5a5f2b1ff4099a8c19aa6288", $category->getPhotoId());
        $this->assertEquals(0, $category->getSubcategoryCount());
        $this->assertEquals("/telefony-komorkowe", $category->getUrl());
        $this->assertEquals("picture", $category->getViewType());

        $path = $category->getPath();
        $this->assertCount(2, $path);
        /** @var Path $pathRow */
        $pathRow = $path[0];
        $this->assertEquals(686, $pathRow->getId());
        $this->assertEquals("/telefony-i-akcesoria", $pathRow->getUrl());
        $this->assertEquals("Telefony i akcesoria", $pathRow->getTitle());
        $pathRow = $path[1];
        $this->assertEquals(687, $pathRow->getId());
        $this->assertEquals("/telefony-komorkowe", $pathRow->getUrl());
        $this->assertEquals("Telefony komórkowe", $pathRow->getTitle());
    }

    public function testFetchById()
    {
        $this->clientApiMock->expects($this->once())->method('send')
            ->will($this->returnValue($this->getCategoryFromApiClient()));

        /** @var Category[] $categories */
        $category = $this->sut->fetchById(687);

        $this->assertEquals(687, $category->getId());
        $this->assertEquals(0.24, $category->getCpcValue());
        $this->assertEquals(2, $category->getDepth());
        $this->assertEquals(null, $category->getIsAdult());
        $this->assertEquals(true, $category->getIsVisible());
        $this->assertEquals("Telefony komórkowe", $category->getTitle());
        $this->assertEquals(686, $category->getParentId());
        $this->assertEquals("07b3eb9f5a5f2b1ff4099a8c19aa6288", $category->getPhotoId());
        $this->assertEquals(0, $category->getSubcategoryCount());
        $this->assertEquals("/telefony-komorkowe", $category->getUrl());
        $this->assertEquals("picture", $category->getViewType());

        $path = $category->getPath();
        $this->assertCount(2, $path);
        /** @var Path $pathRow */
        $pathRow = $path[0];
        $this->assertEquals(686, $pathRow->getId());
        $this->assertEquals("/telefony-i-akcesoria", $pathRow->getUrl());
        $this->assertEquals("Telefony i akcesoria", $pathRow->getTitle());
        $pathRow = $path[1];
        $this->assertEquals(687, $pathRow->getId());
        $this->assertEquals("/telefony-komorkowe", $pathRow->getUrl());
        $this->assertEquals("Telefony komórkowe", $pathRow->getTitle());
    }

    public function testFetchByParentIdWithChildren()
    {
        $categoriesWithChildrenFromApi = $this->getCategoriesWithChildrenFromApiClient();
        $this->clientApiMock->expects($this->once())->method('send')
            ->will($this->returnValue($categoriesWithChildrenFromApi));

        /** @var Category[] $categories */
        $categories = $this->sut->fetchByParentIdWithChildren(687);

        $this->assertCount(7, $categories);

        $allCountCategories = 0;
        foreach ($categories as $category) {
            ++$allCountCategories;
            if ($category->getChildren()) {
                $this->assertParentId($category, $allCountCategories);
            }
        }
        $this->assertEquals(count($categoriesWithChildrenFromApi->categories), $allCountCategories);
    }

    private function assertParentId(Category $category, &$allCountCategories)
    {
        foreach ($category->getChildren() as $child) {
            ++$allCountCategories;
            $this->assertEquals($category->getId(), $child->getParentId());
            if ($child->getChildren()) {
                $this->assertParentId($child, $allCountCategories);
            }
        }
    }

    private function getCategoryFromApiClient()
    {
        return json_decode('{
           "id": 687,
           "cpc_value": 0.24,
           "depth": 2,
           "is_adult": null,
           "is_visible": true,
           "title": "Telefony komórkowe",
           "parent_id": "686",
           "path": [
            {
             "id": 686,
             "title": "Telefony i akcesoria",
             "url": "/telefony-i-akcesoria"
            },
            {
             "id": 687,
             "title": "Telefony komórkowe",
             "url": "/telefony-komorkowe"
            }
           ],
           "photo_id": "07b3eb9f5a5f2b1ff4099a8c19aa6288",
           "subcategory_count": 0,
           "url": "/telefony-komorkowe",
           "view_type": "picture"
        }');
    }

    private function getCategoriesFromApiClient()
    {
        return json_decode('{
         "categories": [
          {
           "id": 687,
           "cpc_value": 0.24,
           "depth": 2,
           "is_adult": null,
           "is_visible": true,
           "title": "Telefony komórkowe",
           "parent_id": "686",
           "path": [
            {
             "id": 686,
             "title": "Telefony i akcesoria",
             "url": "/telefony-i-akcesoria"
            },
            {
             "id": 687,
             "title": "Telefony komórkowe",
             "url": "/telefony-komorkowe"
            }
           ],
           "photo_id": "07b3eb9f5a5f2b1ff4099a8c19aa6288",
           "subcategory_count": 0,
           "url": "/telefony-komorkowe",
           "view_type": "picture"
          }
         ]
        }');
    }

    private function getCategoriesWithChildrenFromApiClient()
    {
        return json_decode('{
        "categories": [
        {
            "id": 87,
            "cpc_value": null,
            "depth": 2,
            "is_adult": null,
            "is_visible": true,
            "title": "Akcesoria fotograficzne",
            "parent_id": "86",
            "photo_id": "7dd76bc102aff4030a62e86b47253d26",
            "subcategory_count": 42,
            "url": "/akcesoria-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 99,
            "cpc_value": null,
            "depth": 2,
            "is_adult": null,
            "is_visible": true,
            "title": "Obiektywy i optyka fotograficzna",
            "parent_id": "86",
            "photo_id": "d7c5cdd6e10f15af58520081eff7aabf",
            "subcategory_count": 7,
            "url": "/obiektywy-i-optyka-fotograficzna",
            "view_type": "list"
        },
        {
            "id": 110,
            "cpc_value": null,
            "depth": 2,
            "is_adult": null,
            "is_visible": true,
            "title": "Aparaty fotograficzne",
            "parent_id": "86",
            "photo_id": "fd243de5375c1336c3d836821e0e4e0a",
            "subcategory_count": 5,
            "url": "/aparaty-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 8467,
            "cpc_value": 0.22,
            "depth": 2,
            "is_adult": null,
            "is_visible": true,
            "title": "Pozostała fotografia i optyka",
            "parent_id": "86",
            "photo_id": "43dc3c7e81485f712dc92676d850fed1",
            "subcategory_count": 0,
            "url": "/pozostala-fotografia-i-optyka",
            "view_type": "list"
        },
        {
            "id": 8485,
            "cpc_value": 0.22,
            "depth": 2,
            "is_adult": null,
            "is_visible": true,
            "title": "Usługi fotograficzne",
            "parent_id": "86",
            "photo_id": "852a8bc294cf1893bb1732773e5f3104",
            "subcategory_count": 0,
            "url": "/uslugi-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 10920,
            "cpc_value": null,
            "depth": 2,
            "is_adult": null,
            "is_visible": true,
            "title": "Sprzęt optyczny",
            "parent_id": "86",
            "photo_id": "4ee9c8238bce1ef40ebcc4d3a2a8a146",
            "subcategory_count": 7,
            "url": "/sprzet-optyczny",
            "view_type": "list"
        },
        {
            "id": 10928,
            "cpc_value": null,
            "depth": 2,
            "is_adult": null,
            "is_visible": true,
            "title": "Zasilanie sprzętu fotograficznego",
            "parent_id": "86",
            "photo_id": "195b8211ed2173101e3a6abf87ffe825",
            "subcategory_count": 4,
            "url": "/zasilanie-sprzetu-fotograficznego",
            "view_type": "list"
        },
        {
            "id": 90,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Fotobanki",
            "parent_id": "87",
            "photo_id": "91d26d5c2ad6e399e9f15be1396a8c2b",
            "subcategory_count": 0,
            "url": "/fotobanki",
            "view_type": "list"
        },
        {
            "id": 92,
            "cpc_value": 0.23,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Filtry fotograficzne",
            "parent_id": "99",
            "photo_id": "16f16fbc2e87992908a97bbd94049ccd",
            "subcategory_count": 0,
            "url": "/filtry-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 94,
            "cpc_value": 0.24,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Karty pamięci",
            "parent_id": "87",
            "photo_id": "c0c5d5820fd2dfff896a5443e3cded48",
            "subcategory_count": 0,
            "url": "/karty-pamieci",
            "view_type": "list"
        },
        {
            "id": 96,
            "cpc_value": 0.26,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Lampy błyskowe",
            "parent_id": "87",
            "photo_id": "4f707d41d85629c94d7ae25f362f0615",
            "subcategory_count": 0,
            "url": "/lampy-blyskowe",
            "view_type": "list"
        },
        {
            "id": 98,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Ładowarki i zasilacze",
            "parent_id": "10928",
            "photo_id": "a0a557b7cc7cb0d5120d2ffea713c3b2",
            "subcategory_count": 0,
            "url": "/ladowarki-i-zasilacze",
            "view_type": "list"
        },
        {
            "id": 103,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Statywy fotograficzne",
            "parent_id": "87",
            "photo_id": "c696334f4851a44f5531b85210c2e994",
            "subcategory_count": 0,
            "url": "/statywy-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 104,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Futerały fotograficzne",
            "parent_id": "87",
            "photo_id": "d2dc74cfbab03cec8b791638684cc031",
            "subcategory_count": 0,
            "url": "/futeraly-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 105,
            "cpc_value": 0.24,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Konwertery fotograficzne",
            "parent_id": "99",
            "photo_id": "6dcc83bf1df1ba80e8b38dd056632719",
            "subcategory_count": 0,
            "url": "/konwertery-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 107,
            "cpc_value": 0.24,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Aparaty analogowe",
            "parent_id": "110",
            "photo_id": "c8a7c138407b40cc1a64828ab2a99bdd",
            "subcategory_count": 0,
            "url": "/aparaty-analogowe",
            "view_type": "list"
        },
        {
            "id": 665,
            "cpc_value": 0.27,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Lornetki",
            "parent_id": "10920",
            "photo_id": "b42c70aa0dea704cb0ba8a4714639f05",
            "subcategory_count": 0,
            "url": "/lornetki",
            "view_type": "list"
        },
        {
            "id": 5784,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Pozostałe akcesoria fotograficzne",
            "parent_id": "87",
            "photo_id": "f6fffe36e58405fb32451f07e3916c50",
            "subcategory_count": 0,
            "url": "/pozostale-akcesoria-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 5785,
            "cpc_value": null,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Fotograficzne akcesoria studyjne",
            "parent_id": "87",
            "photo_id": "b07173979f172e094e03bf9259258b30",
            "subcategory_count": 7,
            "url": "/fotograficzne-akcesoria-studyjne",
            "view_type": "list"
        },
        {
            "id": 5795,
            "cpc_value": 0.27,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Lustrzanki cyfrowe",
            "parent_id": "110",
            "photo_id": "cfb824c81ae93d747e98e771ffd79982",
            "subcategory_count": 0,
            "url": "/lustrzanki-cyfrowe",
            "view_type": "list"
        },
        {
            "id": 5796,
            "cpc_value": 0.26,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Aparaty cyfrowe",
            "parent_id": "110",
            "photo_id": "bc36a8f5d27e73000748e1ee05707264",
            "subcategory_count": 0,
            "url": "/aparaty-cyfrowe",
            "view_type": "list"
        },
        {
            "id": 7202,
            "cpc_value": 0.26,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Cyfrowe ramki na zdjęcia",
            "parent_id": "87",
            "photo_id": "356b21cd421ee046d6c8a125a8119247",
            "subcategory_count": 0,
            "url": "/cyfrowe-ramki-na-zdjecia",
            "view_type": "list"
        },
        {
            "id": 8276,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Albumy na zdjęcia",
            "parent_id": "87",
            "photo_id": "8b7a8a0753f789e881632993fe58a24c",
            "subcategory_count": 0,
            "url": "/albumy-na-zdjecia",
            "view_type": "list"
        },
        {
            "id": 8444,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Pozostałe aparaty fotograficzne",
            "parent_id": "110",
            "photo_id": "74d5b3674593192d9b0ff577ce273a8f",
            "subcategory_count": 0,
            "url": "/pozostale-aparaty-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 8445,
            "cpc_value": 0.24,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Czytniki kart flash",
            "parent_id": "87",
            "photo_id": "a9eabf169573914ab6589d65599b57a8",
            "subcategory_count": 0,
            "url": "/czytniki-kart-flash",
            "view_type": "list"
        },
        {
            "id": 8446,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Tuleje i pierścienie redukcyjne",
            "parent_id": "99",
            "photo_id": "017f337cd681df531141359741a1f7f5",
            "subcategory_count": 0,
            "url": "/tuleje-i-pierscienie-redukcyjne",
            "view_type": "list"
        },
        {
            "id": 8447,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Piloty i wężyki spustowe",
            "parent_id": "87",
            "photo_id": "491f532c94a8f364a9ea4f80546402ac",
            "subcategory_count": 0,
            "url": "/piloty-i-wezyki-spustowe",
            "view_type": "list"
        },
        {
            "id": 8449,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Matówki",
            "parent_id": "87",
            "photo_id": "ce95b3e72758d3f37a2be62792390972",
            "subcategory_count": 0,
            "url": "/matowki",
            "view_type": "list"
        },
        {
            "id": 8450,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Kable, przewody i stacje dokujące",
            "parent_id": "87",
            "photo_id": "96299b23ac76fbe31158203a5a41cee8",
            "subcategory_count": 0,
            "url": "/kable-przewody-i-stacje-dokujace",
            "view_type": "list"
        },
        {
            "id": 8451,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Osłony na obiektyw",
            "parent_id": "99",
            "photo_id": "9c7e63b0699abf76243a3f46a48a4c18",
            "subcategory_count": 0,
            "url": "/oslony-na-obiektyw",
            "view_type": "list"
        },
        {
            "id": 8468,
            "cpc_value": 0.24,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Fotografia podwodna",
            "parent_id": "87",
            "photo_id": "fe3f1477f462b601c4c8d55e5773a6f3",
            "subcategory_count": 0,
            "url": "/fotografia-podwodna",
            "view_type": "list"
        },
        {
            "id": 8470,
            "cpc_value": 0.2,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Środki czyszczące",
            "parent_id": "87",
            "photo_id": "c35af94f020425ee72f5b7a71aeb8e4e",
            "subcategory_count": 0,
            "url": "/srodki-czyszczace",
            "view_type": "list"
        },
        {
            "id": 9323,
            "cpc_value": 0.25,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Teleskopy",
            "parent_id": "10920",
            "photo_id": "c02f5f1a598ec46c31cc0096718ae75b",
            "subcategory_count": 0,
            "url": "/teleskopy",
            "view_type": "list"
        },
        {
            "id": 10917,
            "cpc_value": 0.23,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Lustrzanki analogowe",
            "parent_id": "110",
            "photo_id": "6393c18c53a81280961956eb31ed594e",
            "subcategory_count": 0,
            "url": "/lustrzanki-analogowe",
            "view_type": "list"
        },
        {
            "id": 10918,
            "cpc_value": 0.26,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Obiektywy fotograficzne",
            "parent_id": "99",
            "photo_id": "4df0eeb0d42eb9df0e7ec87ab12d5a27",
            "subcategory_count": 0,
            "url": "/obiektywy-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 10919,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Pozostała optyka fotograficzna",
            "parent_id": "99",
            "photo_id": "5757af75cc8faf9953a145b67f48061d",
            "subcategory_count": 0,
            "url": "/pozostala-optyka-fotograficzna",
            "view_type": "list"
        },
        {
            "id": 10921,
            "cpc_value": 0.24,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Fotografia makro",
            "parent_id": "87",
            "photo_id": "98001676ab0361720ff4a4df7ebf42d8",
            "subcategory_count": 0,
            "url": "/fotografia-makro",
            "view_type": "list"
        },
        {
            "id": 10922,
            "cpc_value": 0.27,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Lunety",
            "parent_id": "10920",
            "photo_id": "1229e656a5390bd5deb6ac0da9c11cfb",
            "subcategory_count": 0,
            "url": "/lunety",
            "view_type": "list"
        },
        {
            "id": 10923,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Lupy",
            "parent_id": "10920",
            "photo_id": "c93b68fa754627573a2f5a72f9499d1c",
            "subcategory_count": 0,
            "url": "/lupy",
            "view_type": "list"
        },
        {
            "id": 10924,
            "cpc_value": 0.24,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Pozostały sprzęt optyczny",
            "parent_id": "10920",
            "photo_id": "7bd01e6dfd28ac8e7f2319359eca5184",
            "subcategory_count": 0,
            "url": "/pozostaly-sprzet-optyczny",
            "view_type": "list"
        },
        {
            "id": 10925,
            "cpc_value": 0.26,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Mikroskopy",
            "parent_id": "10920",
            "photo_id": "95078c197cd78b9c1baebe3a4d9a9d00",
            "subcategory_count": 0,
            "url": "/mikroskopy",
            "view_type": "list"
        },
        {
            "id": 10926,
            "cpc_value": 0.2,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Akcesoria do optyki",
            "parent_id": "10920",
            "photo_id": "0f0cc334bcf326bbf5f4a025f8991b47",
            "subcategory_count": 0,
            "url": "/akcesoria-do-optyki",
            "view_type": "list"
        },
        {
            "id": 10927,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Filmy i klisze",
            "parent_id": "87",
            "photo_id": "64ec17786169b3d42010d8b90d133b2f",
            "subcategory_count": 0,
            "url": "/filmy-i-klisze",
            "view_type": "list"
        },
        {
            "id": 10929,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Gripy",
            "parent_id": "10928",
            "photo_id": "195c5b1501b164545dfd18d61f36b00c",
            "subcategory_count": 0,
            "url": "/gripy",
            "view_type": "list"
        },
        {
            "id": 10930,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Pozostałe zasilanie sprzętu fotograficznego",
            "parent_id": "10928",
            "photo_id": "816c5f48594c090ed238da51d809e3fc",
            "subcategory_count": 0,
            "url": "/pozostale-zasilanie-sprzetu-fotograficznego",
            "view_type": "list"
        },
        {
            "id": 10931,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Akumulatory dedykowane",
            "parent_id": "10928",
            "photo_id": "8da541ebe77e816fdb229a03c5c54166",
            "subcategory_count": 0,
            "url": "/akumulatory-dedykowane",
            "view_type": "list"
        },
        {
            "id": 10932,
            "cpc_value": 0.2,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Folie ochronne i osłony LCD",
            "parent_id": "87",
            "photo_id": "ac1bdc55ddd3ba6a1b3c0f872e0e144d",
            "subcategory_count": 0,
            "url": "/folie-ochronne-i-oslony-lcd",
            "view_type": "list"
        },
        {
            "id": 10933,
            "cpc_value": 0.2,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Muszle do aparatów",
            "parent_id": "87",
            "photo_id": "fc05f47f5b0317e3d2bbb05eea0adc01",
            "subcategory_count": 0,
            "url": "/muszle-do-aparatow",
            "view_type": "list"
        },
        {
            "id": 10934,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Wizjery",
            "parent_id": "87",
            "photo_id": "b65b936cbe369b997f7e9a6678559894",
            "subcategory_count": 0,
            "url": "/wizjery",
            "view_type": "list"
        },
        {
            "id": 10935,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Plecaki fotograficzne",
            "parent_id": "87",
            "photo_id": "663ab372bd4a9d32046c3ce8ceeb433d",
            "subcategory_count": 0,
            "url": "/plecaki-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 10936,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Torby fotograficzne",
            "parent_id": "87",
            "photo_id": "b4fc17062af7f5ab0185dcc9e940019d",
            "subcategory_count": 0,
            "url": "/torby-fotograficzne",
            "view_type": "list"
        },
        {
            "id": 10937,
            "cpc_value": 0.2,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Paski do aparatów",
            "parent_id": "87",
            "photo_id": "8889d521de8611febe62325c8726b395",
            "subcategory_count": 0,
            "url": "/paski-do-aparatow",
            "view_type": "list"
        },
        {
            "id": 10938,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Światłomierze",
            "parent_id": "87",
            "photo_id": "e6adfa04dd3e4c470cf170799cdc265a",
            "subcategory_count": 0,
            "url": "/swiatlomierze",
            "view_type": "list"
        },
        {
            "id": 10939,
            "cpc_value": 0.22,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Głowice do statywów",
            "parent_id": "87",
            "photo_id": "628d3fc73081f4e8ce9693c6eb0710f4",
            "subcategory_count": 0,
            "url": "/glowice-do-statywow",
            "view_type": "list"
        },
        {
            "id": 10945,
            "cpc_value": null,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Akcesoria do ciemni",
            "parent_id": "87",
            "photo_id": "1ca1745caeaf254775c1a1dd0f50b87a",
            "subcategory_count": 7,
            "url": "/akcesoria-do-ciemni",
            "view_type": "list"
        },
        {
            "id": 10954,
            "cpc_value": 0.27,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Dekielki do obiektywów",
            "parent_id": "99",
            "photo_id": "fcf141495a0cc3222ca363aa02e9f80a",
            "subcategory_count": 0,
            "url": "/dekielki-do-obiektywow",
            "view_type": "list"
        },
        {
            "id": 11429,
            "cpc_value": 0.26,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Dyfuzory do lamp",
            "parent_id": "87",
            "photo_id": "10382c11540afb19e5b6b5fbce4cbdf2",
            "subcategory_count": 0,
            "url": "/dyfuzory-do-lamp",
            "view_type": "list"
        },
        {
            "id": 11751,
            "cpc_value": 0.24,
            "depth": 3,
            "is_adult": null,
            "is_visible": true,
            "title": "Adaptery i płytki mocujące do statywów",
            "parent_id": "87",
            "photo_id": "cba7832c11e0990d01015709b598cbe0",
            "subcategory_count": 0,
            "url": "/adaptery-i-plytki-mocujace-do-statywow",
            "view_type": "list"
        }
        ]}');
    }

} 