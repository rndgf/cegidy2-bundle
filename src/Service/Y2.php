<?php
/**
 * @license   Tous droits réservés
 * @author    Renaud Gouffé (renaud@colorz.fr)
 * @copyright Copyright (c) 2022 Colorz (http://www.colorz.fr)
 */

namespace Colorz\Cegid\Service;

use Psr\Log\LoggerInterface;
use SoapFault;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface as ParameterBagInterfaceAlias;
use Symfony\Component\HttpFoundation\RequestStack;
use Y2\Customer\AddNewCustomer;
use Y2\Customer\CustomerInsertData;
use Y2\Customer\CustomerSearchDataType;
use Y2\Customer\CustomerWcfService;
use Y2\Customer\RetailContext as CustomerRetailContext;
use Y2\Customer\SearchCustomerIds;

class Y2
{
    /* @var LoggerInterface */
    protected LoggerInterface $logger;

    /* @var ParameterBagInterfaceAlias */
    protected ParameterBagInterfaceAlias $parameters;

    /* @var RequestStack */
    protected RequestStack $requestStack;

    protected array               $cegidParameters;
    private CustomerRetailContext $retailContext;

    /**
     * AbstractModel constructor.
     * @param RequestStack $requestStack
     * @param ParameterBagInterfaceAlias $parameters
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestStack               $requestStack,
        ParameterBagInterfaceAlias $parameters,
        LoggerInterface            $logger
    ) {
        $this->requestStack    = $requestStack;
        $this->logger          = $logger;
        $this->parameters      = $parameters;
        $this->cegidParameters = $this->parameters->get('cegid') ?? [];

        // Init CEGID SOAP Client

        $this->retailContext = new CustomerRetailContext();
        $this->retailContext->setDatabaseId($this->cegidParameters["db_id"]);
    }

    public function helloWorld()
    {
        echo "Hello World";
    }

    /**
     * @param $customerEmail
     * @return bool
     * @throws SoapFault
     */
    public function checkIfCustomerExist($customerEmail): bool
    {
        $customerSearchDataType = new CustomerSearchDataType();
        $customerSearchData     = $customerSearchDataType->setDirectMail($customerEmail);
        $searchCustomerIds      = new SearchCustomerIds($customerSearchData, $this->retailContext);
        $result                 = $this->getCustomerService()
                                       ->SearchCustomerIds($searchCustomerIds)
                                       ->getSearchCustomerIdsResult();
        $this->logger->debug($result);

        return false;
    }

    public function getCustomerService()
    {
        return new CustomerWcfService($this->getWsdlUrl(), [
            'login'    => $this->getLogin(),
            'password' => $this->getPassword()
        ]);
    }

    public function getWsdlUrl()
    {
        return $this->cegidParameters["wsdl"];
    }

    public function getLogin()
    {
        return $this->cegidParameters["login"];
    }

    public function getPassword()
    {
        return $this->cegidParameters["password"];
    }
}
