// Locale
import './i18n.js';
import gemIcons from './icons';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;

const {
  registerBlockType
} = wp.blocks;

const {
  SelectControl
} = wp.components;

/**
 * Register block
 */
export default registerBlockType( 'godaddy-email-marketing-sign-up-forms/gem-block', {
  title: __( 'GoDaddy Email Marketing', 'godaddy-email-marketing-sign-up-forms' ),
  description: __( 'Display a GoDaddy email marketing form.', 'godaddy-email-marketing-sign-up-forms' ),
  category: 'widgets',
  icon: gemIcons.mail,
  keywords: [
    __( 'GoDaddy', 'godaddy-email-marketing-sign-up-forms' ),
    __( 'Email', 'godaddy-email-marketing-sign-up-forms' ),
    __( 'Form', 'godaddy-email-marketing-sign-up-forms' ),
  ],

  attributes: {
    form: {
      type: 'string',
      sourece: 'text',
      default: gem.isConnected ? gem.forms[0].value : undefined,
    },
  },

  edit: props => {

    const { attributes: { form }, isSelected, className, setAttributes } = props;

    return [

      // Admin Block Markup
      <div className={ className } key={ className }>
        <div className="gem-forms">
          { isSelected ? (
            getFormSelect( form, setAttributes )
          ) : ( <div className="gem-form"><img src={ gem.preloaderUrl } className="preloader" />{ renderGemForm( form ) }</div> ) }
        </div>
      </div>

    ];
  },

  save: props => {
    const { attributes: { form }, className } = props;

    if ( ! gem.isConnected ) {

      return;

    }

    return ( '[gem id=' + form + ']' );
  },
} );

/**
 * Generate the GoDaddy Email Marketing form select field.
 *
 * @param  {integer}  form          Form ID
 * @param  {function} setAttributes Set attributes method.
 */
function getFormSelect( form, setAttributes ) {

  if ( ! gem.isConnected ) {

    return notConnectedError();

  }

  return <SelectControl
    className="form"
    label={ __( 'GoDaddy Email Marketing Form', 'godaddy-email-marketing-sign-up-forms' ) }
    value={ form }
    options={ gem.forms }
    onChange={ ( form ) => { setAttributes( { form } ) } }
  />

}

/**
 * Render the GoDaddy Email Marketing form markup
 *
 * @param {integer} formID Form ID
 */
function renderGemForm( formID ) {

  if ( ! gem.isConnected ) {

    return notConnectedError();

  }

  var data = {
    'action': 'get_gem_form',
    'formID': formID,
  };

  $.post( ajaxurl, data, function( response ) {

    if ( ! response.success ) {

      $( '.gem-form' ).html( gem.getFormError );

      return;

    }

    $( '.gem-form' ).html( response.data );

  } );

}

/**
 * Render the error message when not connected to the GoDaddy Email Marketing API
 *
 * @return {mixed} Markup for the Not connected error notice.
 */
function notConnectedError() {

  return <div>{ __( 'GoDaddy Email Marketing is not connected.', 'godaddy-email-marketing-sign-up-forms' ) } <p><a class="button button-secondary" href={ gem.settingsURL }> {__( 'Connect Now', 'godaddy-email-marketing-sign-up-forms' ) }</a></p></div>;

}
