<?php
/** @var Foil\Template\Template $t */
$this->layout( 'layouts/ixpv4' );
?>

<?php $this->section( 'title' ) ?>
    <a href="<?= route( 'interfaces/sflow-receiver/list' )?>">SflowReceiver</a>
<?php $this->append() ?>

<?php $this->section( 'page-header-postamble' ) ?>
    <li>Edit SflowReceiver</li>
<?php $this->append() ?>

<?php $this->section( 'page-header-preamble' ) ?>
    <li class="pull-right">
        <div class="btn-group btn-group-xs" role="group">
            <a type="button" class="btn btn-default" href="<?= route( 'interfaces/sflow-receiver/list' ) ?>" title="list">
                <span class="glyphicon glyphicon-th-list"></span>
            </a>
        </div>
    </li>
<?php $this->append() ?>

<?php $this->section('content') ?>

<?= $t->alerts() ?>
    <div class="well">
        <?= Former::open()->method( 'POST' )
            ->action( action( 'Interfaces\SflowReceiverController@store' ) )
            ->customWidthClass( 'col-sm-6' )
        ?>
            <div class="col-sm-6">

                <?= Former::text( 'dst_ip' )
                    ->label( 'Destination IP' )
                    ->blockHelp( 'help text' );
                ?>

                <?= Former::number( 'dst_port' )
                    ->label( 'Destination Port' )
                    ->blockHelp( 'help text' );
                ?>

            </div>

            <?= Former::hidden( 'id' )
                ->value( $t->sflr ? $t->sflr->getId() : null )
            ?>

            <?= Former::hidden( 'viid' )
                ->value( $t->sflr ? $t->sflr->getVirtualInterface()->getId() : $t->vi->getId() )
            ?>

            <?=Former::actions(
                Former::primary_submit( 'Save Changes' ),
                Former::default_link( 'Cancel' )->href( route( 'interfaces/sflow-receiver/list' ) ),
                Former::success_button( 'Help' )->id( 'help-btn' )
            )->id('btn-group');?>

        <?= Former::close() ?>
    </div>

<?php $this->append() ?>

<?php $this->section( 'scripts' ) ?>
    <script>
        /**
         * hide the help block at loading
         */
        $('p.help-block').hide();

        /**
         * display / hide help sections on click on the help button
         */
        $( "#help-btn" ).click( function() {
            $( "p.help-block" ).toggle();
        });

    </script>
<?php $this->append() ?>