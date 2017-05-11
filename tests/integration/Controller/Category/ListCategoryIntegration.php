<?php

namespace Piivo\Bundle\ConnectorBundle\tests\integration\Controller\Category;

use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListCategoryIntegration extends ApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        static::bootKernel();
        self::$count++;

        $this->loadCategory(['code' => 'tree1', 'parent' => null]);
        $this->loadCategory(['code' => 'tree2', 'parent' => null]);
        $this->loadCategory(['code' => 'parent1', 'parent' => 'tree1']);
        $this->loadCategory(['code' => 'parent2', 'parent' => 'tree1']);
        $this->loadCategory(['code' => 'leaf1', 'parent' => 'parent1']);
        $this->loadCategory(['code' => 'leaf2', 'parent' => 'parent2']);
        $this->loadCategory(['code' => 'leaf3', 'parent' => 'tree2']);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->removeCategory('tree1');
        $this->removeCategory('tree2');

        parent::tearDown();
    }

    /**
     * @param array $data
     */
    protected function loadCategory(array $data)
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $this->get('pim_catalog.saver.category')->save($category, ['flush' => false]);
    }

    /**
     * @param string $code
     */
    protected function removeCategory($code)
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier($code);
        $this->get('pim_catalog.remover.category')->remove($category);
    }

    public function testListCategories()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/categories?search={"parent":[{"operator":"EMPTY"}]}');

        $expected = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=false&search=%7B%22parent%22%3A%5B%7B%22operator%22%3A%22EMPTY%22%7D%5D%7D"
        },
        "first": {
            "href": "http://localhost/api/rest/v1/categories?page=1&limit=10&with_count=false&search=%7B%22parent%22%3A%5B%7B%22operator%22%3A%22EMPTY%22%7D%5D%7D"
        }
    },
    "current_page": 1,
    "_embedded": {
        "items": [
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/master"
                    }
                },
                "code": "master",
                "parent": null,
                "labels": {
                    "en_US": "Master catalog"
                }
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/tree1"
                    }
                },
                "code": "tree1",
                "parent": null,
                "labels": {}
            },
            {
                "_links": {
                    "self": {
                        "href": "http://localhost/api/rest/v1/categories/tree2"
                    }
                },
                "code": "tree2",
                "parent": null,
                "labels": {}
            }
        ]
    }
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        var_dump(Configuration::getTechnicalCatalogPath());

        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }
}
