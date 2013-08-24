<div id="admin_index" class="span10">
    <h4>{{ @tmp.admin.introduction }}</h4>
    <br>

    <!-- Меню с табами -->
    <ul class="nav nav-tabs" id="testr_tab">
        <li class="active"><a href="#t_config">Настройки</a></li>
    </ul>
    <!-- ================ -->

    <!-- Содержимое табов -->
    <div class="tab-content">
        <!-- =================================================================================== -->
        <div class="tab-pane active" id="t_config">
            <include href="/view/admin_config.php" />
        </div>
        <!-- =================================================================================== -->
    </div>
    <!-- ================ -->
</div>