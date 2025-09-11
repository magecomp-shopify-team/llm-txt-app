import React, { useState, useEffect } from "react";
import {
  Page,
  Layout,
  Card,
  Text,
  InlineStack,
  Select,
  Checkbox,
  Box,
  Badge,
  Spinner,
  Button,
  BlockStack,
} from "@shopify/polaris";
import { useAppBridge, SaveBar } from "@shopify/app-bridge-react";
import { deepEqual, fetchApi, getRedirectUrl } from "../../utils/utils";
import { useNavigate } from "react-router-dom";
import { shop_data } from "../../app";
import Footer from "../../components/Footer/Footer";

function formatDate(dateString) {
  if (!dateString) return "Not set";
  const d = new Date(dateString);
  return d.toLocaleString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
}

export default function DashboardPage() {
  const shopify = useAppBridge();
  const navigate = useNavigate();

  const defaultSettings = {
    includeProducts: true,
    includeCollections: true,
    includePages: false,
    includeBlogs: false,
    format: "human",
    syncFrequency: "weekly",
  };
  const [initialSettings, setInitialSettings] = useState({ ...defaultSettings });
  const [settings, setSettings] = useState({ ...defaultSettings });
  const [isSaving, setIsSaving] = useState(false);
  const [loadingSettings, setLoadingSettings] = useState(true);

  const [lastSyncedAt, setLastSyncedAt] = useState(null);
  const [nextSyncedAt, setNextSyncedAt] = useState(null);
  const [counts, setCounts] = useState({
    products: 0,
    collections: 0,
    pages: 0,
    blogs: 0,
  });
  const [loadingCounts, setLoadingCounts] = useState(true);

  const handleClickPreview = () => {
    const url = 'https://' + shop_data.name + "/llms.txt";
    window.open(url, "_blank");
  }

  useEffect(() => {
    if (!deepEqual(settings, initialSettings)) {
      shopify.saveBar.show("feed-settings");
    } else {
      shopify.saveBar.hide("feed-settings");
    }
  }, [settings, initialSettings]);

  async function fetchSettings() {
    try {
      setLoadingSettings(true);
      const data = await fetchApi("get", "/api/llm-settings");
      const s = data?.settings || {};

      const mergedSettings = {
        ...defaultSettings,
        includeProducts:
          s.includeProducts ?? defaultSettings.includeProducts,
        includeCollections:
          s.includeCollections ?? defaultSettings.includeCollections,
        includePages: s.includePages ?? defaultSettings.includePages,
        includeBlogs: s.includeBlogs ?? defaultSettings.includeBlogs,
        format: s.format ?? defaultSettings.format,
        syncFrequency: s.syncFrequency ?? defaultSettings.syncFrequency,
      };

      setSettings({ ...mergedSettings });
      setInitialSettings({ ...mergedSettings });

      setLastSyncedAt(data?.lastSyncedAt || null);
      setNextSyncedAt(data?.nextSyncedAt || null);
    } catch (err) {
      console.error("Failed to fetch settings", err);
    } finally {
      setLoadingSettings(false);
    }
  }

  useEffect(() => {


    async function fetchCounts() {
      try {
        setLoadingCounts(true);
        const data = await fetchApi("get", "/api/llm/counts");
        setCounts(
          data || { products: 0, collections: 0, pages: 0, blogs: 0 }
        );
      } catch (err) {
        console.error("Failed to fetch counts", err);
      } finally {
        setLoadingCounts(false);
      }
    }

    fetchSettings();
    fetchCounts();
  }, []);

  function updateSetting(key, value) {
    setSettings((prev) => ({ ...prev, [key]: value }));
  }

  async function handleSaveSettings() {
    setIsSaving(true);
    try {
      const data = await fetchApi("post", "/api/llm-settings", {
        body: settings,
      });
      if (data?.message) {
        shopify.toast.show("Settings saved successfully");
        fetchSettings();
      } else {
        shopify.toast.show("Something went wrong 5", { isError: true });
      }
    } catch (err) {
      shopify.toast.show("Something went wrong", { isError: true });
    } finally {
      setIsSaving(false);
    }
  }

  async function handleGenerate() {
    setIsSaving(true);
    try {
      const data = await fetchApi("get", "/api/llm/generate");

      if (data?.url) {
        shopify.toast.show("Feed generated successfully");
        fetchSettings();
      } else {
        shopify.toast.show("Failed to generate feed", { isError: true });
      }
    } catch (err) {
      console.error("Generate failed", err);
      shopify.toast.show("Error generating feed", { isError: true });
    } finally {
      setIsSaving(false);
    }
  }

  function handleDiscardSettings() {
    setSettings({ ...initialSettings });
    shopify.saveBar.hide("feed-settings");
  }

  return (
    <Page title="LLM Feed Generator">
      <Layout>
        {/* Feed settings */}
        <Layout.Section>
          <Card>
            <Box opacity={loadingSettings ? 0.5 : 1} pointerEvents={loadingSettings ? 'none' : 'auto'}>
              <BlockStack gap="400">
                <Text as="h2" variant="headingMd">
                  LLMs settings
                </Text>

                <InlineStack wrap={false} gap="400">
                  <Checkbox
                    label="Include Products"
                    checked={settings.includeProducts}
                    onChange={(val) => updateSetting("includeProducts", val ? 1 : 0)}
                  />
                  <Checkbox
                    label="Include Collections"
                    checked={settings.includeCollections}
                    onChange={(val) => updateSetting("includeCollections", val ? 1 : 0)}
                  />
                  <Checkbox
                    label="Include Pages"
                    checked={settings.includePages}
                    onChange={(val) => updateSetting("includePages", val ? 1 : 0)}
                  />
                  <Checkbox
                    label="Include Blogs"
                    checked={settings.includeBlogs}
                    onChange={(val) => updateSetting("includeBlogs", val ? 1 : 0)}
                  />
                </InlineStack>
                <Box width="250px">
                  <Select
                    label="Sync frequency"
                    options={[
                      { label: "Daily", value: "daily" },
                      { label: "Weekly", value: "weekly" },
                      { label: "Monthly", value: "monthly" },
                    ]}
                    value={settings.syncFrequency}
                    onChange={(v) => updateSetting("syncFrequency", v)}
                  />
                </Box>
              </BlockStack>
            </Box>
          </Card>
        </Layout.Section>

        {/* Status + Plan */}
        <Layout.Section>
          <Card>
            <BlockStack gap="200">
              <Box>
                <InlineStack align="space-between">
                  <Box width="max-content">
                    <Text as="h2" variant="headingMd">
                      Status
                    </Text>
                  </Box>
                  <Box width="max-content">
                    <Button
                      onClick={handleGenerate}
                      loading={isSaving}
                    >
                      Generate Now
                    </Button>
                  </Box>

                </InlineStack>
              </Box>
              <Text as="p">
                Last synced: {formatDate(lastSyncedAt)}
              </Text>
              <Text as="p">
                Next scheduled sync: {formatDate(nextSyncedAt)}
              </Text>
              <Text as="p">
                Public URL: <Button variant="plain" onClick={handleClickPreview}><code>/llms.txt</code></Button>
              </Text>


              {loadingCounts ? (
                <Box padding="400">
                  <Spinner
                    accessibilityLabel="Loading counts"
                    size="small"
                  />
                </Box>
              ) : (
                <Box>
                  <Box paddingBlock="200">
                    <Text as="h2" variant="headingMd">
                      Current content in your store
                    </Text>
                  </Box>
                  <InlineStack gap="600" wrap>
                    <Box minWidth="70px">
                      <Badge tone="info">{counts.products} products</Badge>
                    </Box>
                    <Box minWidth="70px">
                      <Badge tone="success">{counts.collections} collections</Badge>
                    </Box>
                    <Box minWidth="70px">
                      <Badge tone="attention">{counts.pages} pages</Badge>
                    </Box>
                    <Box minWidth="70px">
                      <Badge tone="magic">{counts.blogs} blogs</Badge>
                    </Box>
                  </InlineStack>
                </Box>
              )}
            </BlockStack>
          </Card>
        </Layout.Section>
      </Layout>
      <Footer />

      {/* SaveBar */}
      <SaveBar id="feed-settings">
        <button onClick={handleDiscardSettings} disabled={isSaving}>Cancel</button>
        <button onClick={handleSaveSettings} disabled={isSaving} variant={"primary"}>Save</button>
      </SaveBar>
    </Page>
  );
}
