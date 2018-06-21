<?php

namespace Statamic\Addons\SeoPro\Reporting;

use Statamic\API\Entry;

class Page
{
    protected $report;
    protected $data;
    protected $results;

    protected $rules = [
        Rules\Page\UniqueTitleTag::class,
        Rules\Page\UniqueMetaDescription::class,
        Rules\Page\NoUnderscoresInUrl::class,
        Rules\Page\ThreeSegmentUrls::class,
    ];

    public function setData($data)
    {
        $this->data = collect($data);

        return $this;
    }

    public function setReport(Report $report)
    {
        $this->report = $report;

        return $this;
    }

    public function report()
    {
        return $this->report;
    }

    public function results()
    {
        return $this->results;
    }

    public function validate()
    {
        $results = [];

        foreach ($this->rules as $class) {
            $rule = new $class;

            $rule->setPage($this)->process();

            $results[$rule->id()] = $rule->save();
        }

        $this->results = $results;
    }

    public function get($key)
    {
        return $this->data->get($key);
    }

    public function status()
    {
        $status = 'pass';

        foreach ($this->getRuleResults() as $result) {
            if ($result['status'] === 'warning') {
                $status = 'warning';
            }

            if ($result['status'] === 'fail') {
                return 'fail';
            }
        }

        return $status;
    }

    public function getRuleResults()
    {
        $results = collect();

        foreach ($this->results as $class => $array) {
            $class = "Statamic\\Addons\\SeoPro\\Reporting\\Rules\\Page\\$class";
            $rule = new $class;
            $rule->setPage($this);
            $rule->load($array);

            $results[] = [
                'description' => $rule->description(),
                'status' => $rule->status(),
                'comment' => $rule->comment(),
            ];
        }

        return $results;
    }

    public function url()
    {
        return $this->get('canonical_url');
    }

    public function id()
    {
        return md5($this->url());
    }
}