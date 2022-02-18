/* eslint-disable react/react-in-jsx-scope */
import { InspectorControls } from '@wordpress/block-editor';

export const name = 'mailpoet/dummy';

export const settings = {
  title: 'Dummy block',
  description: 'This Is A Dymmy Custom Block',
  category: 'text',
  attributes: {},
  supports: {
    html: false,
    multiple: false,
  },
  edit: (): JSX.Element => (
    <>
      <InspectorControls>
        <p>Dummy Block Controls</p>
      </InspectorControls>
      <p>Dummy</p>
    </>
  ),
  save(): string {
    return '<p>Dummy</p>';
  },
};
