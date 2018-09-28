// Locale
import './i18n.js';
import gemIcons from './icons';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;

const {
  registerBlockType,
  RichText,
  AlignmentToolbar,
  BlockAlignmentToolbar
} = wp.blocks;

const {
  BlockControls,
  InspectorControls
} = wp.editor;

const {
  Toolbar,
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
    title: {
      type: 'string',
      source: 'text',
      selector: '.gem-title',
    },
    form: {
      type: 'array',
      selector: '.form',
      default: ( Object.keys( gem.forms ).length > 0 ) ? gem.forms[0].label : undefined,
    },
  },

  edit: props => {

    const { attributes: { title, form }, isSelected, className, setAttributes } = props;

    return [

      // Admin Block Markup
      <div className={ className } key={ className }>
        <div className="gem-forms">
          { isSelected ? (
            <SelectControl
              label={ __( 'Gem Form', 'godaddy-email-marketing-sign-up-forms' ) }
              value={ form }
              options={ gem.forms }
              onChange={ ( form ) => { setAttributes( { form } ) } }
            />
          ) : ( <h2>{ form }</h2> ) }
        </div>
      </div>

    ];
  },

  save: props => {
    const { attributes: { title, form }, className } = props;

    return;
  },
} );
