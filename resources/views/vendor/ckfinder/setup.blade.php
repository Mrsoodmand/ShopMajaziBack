{{-- Replaces the CKFinder package's setup view (used by its browser page, which the old pages' CKEditor opens):
     the connector route lives in routes/admin.php here, so it is named admin.ckfinder_connector. --}}
<script type="text/javascript" src="/js/ckfinder/ckfinder.js"></script>
<script>CKFinder.config({ connectorPath: '{{ route('admin.ckfinder_connector', [], false) }}', language: 'fa' });</script>
