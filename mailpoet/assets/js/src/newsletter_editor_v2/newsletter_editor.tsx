/* eslint-disable react/react-in-jsx-scope */
import { render } from '@wordpress/element';
import {
  Panel,
  PanelBody,
  Button,
  TextControl,
  SelectControl,
  ColorPalette,
} from '@wordpress/components';
import { registerBlockType } from '@wordpress/blocks';
import IsolatedBlockEditor, { DocumentSection, ToolbarSlot } from '@automattic/isolated-block-editor';
import { name as dummyBlockName, settings as dummyBlockSettings } from './dummy_block';

// Add Custom Block Type
registerBlockType(dummyBlockName, dummyBlockSettings);

const settings = {
  iso: {
    blocks: {
      allowBlocks: [dummyBlockName, 'core/paragraph', 'core/heading', 'core/list', 'core/image', 'core/spacer'],
      disallowBlocks: [],
    },
    toolbar: {
      inserter: true,
      inspector: true,
      navigation: true,
      toc: true,
      documentInspector: true,
    },
    moreMenu: {
      editor: true,
      fullscreen: true,
      preview: true,
      topToolbar: true,
    },
    allowApi: true,
  },
};
const saveContent = (html) => (console.log(html)); // eslint-disable-line no-console
const loadInitialContent = (parse) => {
  const html = '<!-- wp:paragraph -->\n'
    + '<p>Hello reader!</p>\n'
    + '<!-- /wp:paragraph -->';
  return parse(html);
};

render(
  <IsolatedBlockEditor
    settings={settings}
    onSaveContent={(html) => saveContent(html)}
    onLoad={loadInitialContent}
    onError={() => document.location.reload()}
  >
    <ToolbarSlot>
      <Button>Save Draft</Button>
      <Button variant="primary">Send</Button>
    </ToolbarSlot>
    <DocumentSection>
      <Panel>
        <PanelBody title="Sending Settings">
          <TextControl label="Sender name" />
          <TextControl label="Sender address" />
          <SelectControl
            label="List of recipients"
            options={[
              { value: null, label: 'Select a list', disabled: true },
              { value: 1, label: 'Regular customers' },
              { value: 2, label: 'List B' },
            ]}
          />
        </PanelBody>
        <PanelBody title="Style Settings">
          <ColorPalette
            label="Body background"
            colors={[
              { name: 'black', color: '#000' },
              { name: 'red', color: '#f00' },
              { name: 'white', color: '#fff' },
              { name: 'blue', color: '#00f' },
            ]}
          />
          <ColorPalette
            label="Content background"
            colors={[
              { name: 'black', color: '#000' },
              { name: 'red', color: '#f00' },
              { name: 'white', color: '#fff' },
              { name: 'blue', color: '#00f' },
            ]}
          />
        </PanelBody>
      </Panel>
    </DocumentSection>
  </IsolatedBlockEditor>,
  document.querySelector('#mailpoet-email-editor')
);
