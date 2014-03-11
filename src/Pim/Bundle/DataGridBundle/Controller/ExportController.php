<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;

use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;

/**
 * Datagrid controller for export action
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportController
{
    /** @var Request $request */
    protected $request;

    /** @var MassActionParametersParser $parametersParser */
    protected $parametersParser;

    /** @var MassActionDispatcher $massActionDispatcher */
    protected $massActionDispatcher;

    /** @var SerializerInterface $serializer */
    protected $serializer;

    /**
     * Constructor
     *
     * @param Request $request
     * @param MassActionParametersParser $parametersParser
     * @param MassActionDispatcher $massActionDispatcher
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Request $request,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer
    ) {
        $this->request = $request;
        $this->parametersParser     = $parametersParser;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->serializer           = $serializer;
    }

    /**
     * Data export action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        // Export time execution depends on entities exported
        ignore_user_abort(false);
        set_time_limit(0);

        return $this->createStreamedResponse()->send();
    }

    /**
     * Create a streamed response containing a file
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    protected function createStreamedResponse()
    {
        $filename = $this->createFilename();

        $response = new StreamedResponse();
        $attachment = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $attachment);
        $response->setCallback($this->quickExportCallback());

        return $response;
    }

    /**
     * Create filename
     * @return string
     */
    protected function createFilename()
    {
        $dateTime = new \DateTime();

        return sprintf(
            'export_%s.csv',
            $dateTime->format('Y-m-d_H:i:s')
        );
    }

    /**
     * Callback for streamed response
     * Dispatch mass action and returning result as a file
     *
     * @return \Closure
     */
    protected function quickExportCallback()
    {
        return function () {
            flush();

            $format  = 'csv';
            $context = [
                'withHeader'    => true,
                'heterogeneous' => true
            ];

            $parameters  = $this->parametersParser->parse($this->request);
            $requestData = array_merge($this->request->query->all(), $this->request->request->all());

            $results = $this->massActionDispatcher->dispatch(
                $requestData['gridName'],
                $requestData['actionName'],
                $parameters,
                $requestData
            );

            echo $this->serializer->serialize($results, $format, $context);

            flush();
        };
    }
}
