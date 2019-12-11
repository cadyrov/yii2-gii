<?php
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */
$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
$class = $generator->modelClass;
$pk = $class::primaryKey();
$head = $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))));
$stack = $head . 's';
$tableSchema = $generator->getTableSchema();
?>
<template>
    <section>
        <div class="title d-flex justify-content-between">
            <h1 class="pull-left"><?= $head ?></h1>
            <div class="btns_btm top_btns d-flex">
                <a @click="append" id="add_client" >Добавить</a>
            </div>
        </div>
        <div class="row items">
            <table class="table clients table-hover">
                <thead>
                <tr>
                    <?php
                        foreach ($tableSchema->columns as $column) {
                            echo '<th scope="col"><a >' . $column->name . '</a></th>';
                        }
                    ?>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(entity, i) in <?= $stack ?>">
                    <?php
                    foreach ($tableSchema->columns as $column) {
                        if ( $column->type == 'datetime' ||  $column->type == 'timestamp') {
                            echo '<date-picker v-model="report.create_at" :lang="lang" valueType="format" :first-day-of-week="1" @change="update(i)"></date-picker>';
                        } else {
                            echo '<td><input  v-model="entity.' . $column->name . '" @change="update(i)" type="text">';
                        }
                    }
                    ?>
                    <td><a @click="remove(i)" id="delete"></a></td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>


<script>
  import Http from '../../utils/http'
  import { mapGetters } from 'vuex'
  export default {
    name: '<?= $head?>',
    computed: {
      ...mapGetters({
        url: 'app/apiUrl'
      })
    },
    data () {
      return {
        <?= $stack?>: []
      }
    },
    methods: {
      getStack () {
        var that = this
        Http.get(this.url + '<?= $head?>')
          .then(resp => {
            that.<?= $stack?> = resp.data
          })
      },
      update (idx) {
        let entity = this.<?= $stack?>[idx]
        var that = this
        if (entity != null) {
          Http.post(this.url + '<?= $head?>/update', entity)
            .then(resp => {
              that.<?= $stack?>[idx] = resp.data
            })
        }
      },
      append () {
        let that = this
        Http.post(this.url + '<?= $head?>/create')
          .then(resp => {
            that.getStack()
          })
      },
      remove (idx) {
        let entity = this.<?= $stack?>[idx]
        var that = this
        if (entity != null) {
          Http.post(this.url + '<?= $head?>/delete', entity)
            .then(resp => {
              that.getStack()
            })
        }
      }
    },
    created () {
      this.getStack()
    }
  }
</script>
