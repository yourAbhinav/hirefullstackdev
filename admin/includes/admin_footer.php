            </div>
        </div>
    </div>

    <script id="admin-panel-config" type="application/json"><?= json_encode([
        'notificationApi' => appUrl('admin/api/notification_api.php'),
        'userApi' => appUrl('admin/api/user_api.php'),
        'applicationApi' => appUrl('admin/api/application_api.php'),
        'resumeApi' => appUrl('admin/api/resume_api.php'),
        'logsUrl' => appUrl('admin/logs.php'),
        'csrfToken' => csrfToken(),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <script src="<?= appUrl('admin/assets/js/admin-panel.js') ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
</body>
</html>
